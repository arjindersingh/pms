<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SharedMaster;
use App\Support\SharedMasterRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SharedMasterController extends Controller
{
    public function index(Request $request): View
    {
        $key = $request->string('type')->toString() ?: 'qualification-levels';
        $type = SharedMasterRegistry::get($key);
        $model = $type['model'];
        $parentOptions = collect();
        $selectedParentId = null;
        $records = $model::query()->orderBy('sort_order')->orderBy('display_name');

        if (isset($type['parent'])) {
            $parentModel = $type['parent']['model'];
            $parentOptions = $parentModel::available()
                ->when(isset($type['parent']['context']), fn ($query) => $query->with($type['parent']['context']))
                ->get();
        }

        if (($type['scoped_parent'] ?? false) && $parentOptions->isNotEmpty()) {
            $requestedParentId = $request->integer('parent');
            $selectedParentId = $parentOptions->contains('id', $requestedParentId)
                ? $requestedParentId
                : $parentOptions->firstWhere('code', $type['parent']['default_code'] ?? null)?->id ?? $parentOptions->first()->id;
            $records->where($type['parent']['field'], $selectedParentId);
        }

        return view('admin.shared-masters.index', [
            'types' => SharedMasterRegistry::TYPES,
            'selectedKey' => $key,
            'selectedType' => $type,
            'records' => $records->get(),
            'parentOptions' => $parentOptions,
            'selectedParentId' => $selectedParentId,
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $definition = SharedMasterRegistry::get($type);
        $model = $definition['model'];
        $model::create($this->validated($request, new $model, $definition));

        return $this->backTo($type, $definition['label'].' entry created.', $request->integer($definition['parent']['field'] ?? ''));
    }

    public function update(Request $request, string $type, int $record): RedirectResponse
    {
        $definition = SharedMasterRegistry::get($type);
        $item = $this->record($definition['model'], $record);
        $item->update($this->validated($request, $item, $definition));

        return $this->backTo($type, $definition['label'].' entry updated.', $request->integer($definition['parent']['field'] ?? ''));
    }

    public function destroy(string $type, int $record): RedirectResponse
    {
        $definition = SharedMasterRegistry::get($type);
        $item = $this->record($definition['model'], $record);
        $parentId = isset($definition['parent']) ? (int) $item->{$definition['parent']['field']} : null;
        $item->delete();

        return $this->backTo($type, 'Entry archived. Existing historical references remain safe.', $parentId);
    }

    private function validated(Request $request, SharedMaster $record, array $definition): array
    {
        $codePattern = ($definition['code_case'] ?? null) === 'lower'
            ? 'regex:/^[A-Za-z0-9_\-]+$/'
            : 'regex:/^[A-Z0-9_\-]+$/';
        $uniqueCode = Rule::unique($record->getTable(), 'code')->ignore($record->getKey());
        if (($definition['unique_with_parent'] ?? false) && isset($definition['parent'])) {
            $uniqueCode->where($definition['parent']['field'], $request->input($definition['parent']['field']));
        }
        $rules = [
            'code' => ['required', 'string', 'max:40', $codePattern, $uniqueCode],
            'short_name' => ['nullable', 'string', 'max:80'],
            'display_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:99999'],
            'is_active' => ['nullable', 'boolean'],
        ];
        if (isset($definition['parent'])) {
            $parentModel = $definition['parent']['model'];
            $rules[$definition['parent']['field']] = ['required', 'integer', 'exists:'.(new $parentModel)->getTable().',id'];
        }
        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active');
        $data['code'] = ($definition['code_case'] ?? null) === 'lower'
            ? strtolower($data['code'])
            : strtoupper($data['code']);

        return $data;
    }

    /** @param class-string<SharedMaster> $model */
    private function record(string $model, int $id): SharedMaster
    {
        return $model::query()->findOrFail($id);
    }

    private function backTo(string $type, string $message, ?int $parentId = null): RedirectResponse
    {
        return redirect()->route('admin.shared-masters.index', array_filter(['type' => $type, 'parent' => $parentId]))->with('status', $message);
    }
}

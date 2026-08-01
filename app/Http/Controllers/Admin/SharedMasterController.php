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

        return view('admin.shared-masters.index', [
            'types' => SharedMasterRegistry::TYPES,
            'selectedKey' => $key,
            'selectedType' => $type,
            'records' => $model::query()->orderBy('sort_order')->orderBy('display_name')->get(),
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $definition = SharedMasterRegistry::get($type);
        $model = $definition['model'];
        $model::create($this->validated($request, new $model));

        return $this->backTo($type, $definition['label'].' entry created.');
    }

    public function update(Request $request, string $type, int $record): RedirectResponse
    {
        $definition = SharedMasterRegistry::get($type);
        $item = $this->record($definition['model'], $record);
        $item->update($this->validated($request, $item));

        return $this->backTo($type, $definition['label'].' entry updated.');
    }

    public function destroy(string $type, int $record): RedirectResponse
    {
        $definition = SharedMasterRegistry::get($type);
        $this->record($definition['model'], $record)->delete();

        return $this->backTo($type, 'Entry archived. Existing historical references remain safe.');
    }

    private function validated(Request $request, SharedMaster $record): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9_\-]+$/', Rule::unique($record->getTable(), 'code')->ignore($record->getKey())],
            'short_name' => ['nullable', 'string', 'max:80'],
            'display_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:99999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['code'] = strtoupper($data['code']);

        return $data;
    }

    /** @param class-string<SharedMaster> $model */
    private function record(string $model, int $id): SharedMaster
    {
        return $model::query()->findOrFail($id);
    }

    private function backTo(string $type, string $message): RedirectResponse
    {
        return redirect()->route('admin.shared-masters.index', ['type' => $type])->with('status', $message);
    }
}

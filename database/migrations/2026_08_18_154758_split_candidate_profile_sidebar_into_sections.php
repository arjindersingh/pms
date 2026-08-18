<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $parent = DB::table('portal_menus')->where('slug', 'candidate-profile')->first();
        if (! $parent) {
            return;
        }

        DB::table('portal_menus')->where('id', $parent->id)->update(['route_name' => null, 'updated_at' => now()]);
        foreach ($this->sections() as $index => [$name, $section, $icon]) {
            $menuId = DB::table('portal_menus')->insertGetId([
                'portal_module_id' => $parent->portal_module_id,
                'parent_id' => $parent->id,
                'name' => $name,
                'slug' => 'candidate-profile-'.$section,
                'route_name' => 'talent.profile.page.'.$section,
                'icon' => $icon,
                'position' => ($index + 1) * 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->copyPermissions((int) $parent->id, $menuId);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('portal_menus')->whereIn('slug', collect($this->sections())->map(fn (array $section) => 'candidate-profile-'.$section[1]))->delete();
        DB::table('portal_menus')->where('slug', 'candidate-profile')->update(['route_name' => 'talent.profile.edit', 'updated_at' => now()]);
    }

    /** @return array<int, array{string, string, string}> */
    private function sections(): array
    {
        return [
            ['Personal', 'personal', 'bi-person'], ['Photograph', 'photograph', 'bi-camera'],
            ['Contact & Address', 'contact', 'bi-geo-alt'], ['Education', 'education', 'bi-mortarboard'],
            ['Experience', 'experience', 'bi-briefcase'], ['Projects', 'projects', 'bi-kanban'],
            ['Awards & Achievements', 'recognitions', 'bi-trophy'], ['Professional Memberships', 'memberships', 'bi-person-badge'],
            ['References', 'references', 'bi-people'], ['Social & Professional Profiles', 'social', 'bi-share'],
            ['Declarations & Consent', 'declarations', 'bi-shield-check'], ['Publications', 'publications', 'bi-journal-richtext'],
            ['Skills & Languages', 'skills', 'bi-tools'], ['Talents', 'talents', 'bi-stars'],
            ['Hobbies & Interests', 'hobbies', 'bi-heart'], ['Job Preferences', 'preferences', 'bi-sliders2'],
        ];
    }

    private function copyPermissions(int $sourceMenuId, int $targetMenuId): void
    {
        foreach (['portal_menu_user_type' => 'user_type_id', 'portal_menu_user_role' => 'user_role_id', 'portal_menu_user' => 'user_id', 'portal_menu_subscription_plan' => 'subscription_plan_id'] as $table => $ownerColumn) {
            foreach (DB::table($table)->where('portal_menu_id', $sourceMenuId)->get() as $permission) {
                $values = (array) $permission;
                unset($values['id']);
                $values['portal_menu_id'] = $targetMenuId;
                $values['created_at'] = now();
                $values['updated_at'] = now();
                DB::table($table)->updateOrInsert([$ownerColumn => $permission->{$ownerColumn}, 'portal_menu_id' => $targetMenuId], $values);
            }
        }
    }
};

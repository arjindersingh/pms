<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_types')->where('category', 'employer')->update(['category' => 'recruiter']);
        DB::table('user_types')->where('category', 'job_seeker')->update(['category' => 'talent']);

        $this->renameUserType('employer', 'recruiter');
        $this->renameUserType('job-seeker', 'talent');
        $this->renameUserType('corporate-employer', 'corporate-recruiter');

        $this->renameMenu('employer-dashboard', 'recruiter-dashboard', 'recruiter.dashboard');
        $this->renameMenu('employer-applications', 'recruiter-applications');
        $this->renameMenu('job-seeker-dashboard', 'talent-dashboard', 'talent.dashboard');

        DB::table('users')->where('email', 'employer@example.com')->update([
            'name' => 'Demo Recruiter',
            'email' => 'recruiter@example.com',
        ]);
        DB::table('users')->where('email', 'seeker@example.com')->update([
            'name' => 'Demo Talent',
            'email' => 'talent@example.com',
        ]);
    }

    public function down(): void
    {
        $this->renameMenu('recruiter-dashboard', 'employer-dashboard', 'employer.dashboard');
        $this->renameMenu('recruiter-applications', 'employer-applications');
        $this->renameMenu('talent-dashboard', 'job-seeker-dashboard', 'job-seeker.dashboard');

        $this->renameUserType('recruiter', 'employer');
        $this->renameUserType('talent', 'job-seeker');
        $this->renameUserType('corporate-recruiter', 'corporate-employer');

        DB::table('users')->where('email', 'recruiter@example.com')->update([
            'name' => 'Demo Employer',
            'email' => 'employer@example.com',
        ]);
        DB::table('users')->where('email', 'talent@example.com')->update([
            'name' => 'Demo Job Seeker',
            'email' => 'seeker@example.com',
        ]);

        DB::table('user_types')->where('category', 'recruiter')->update(['category' => 'employer']);
        DB::table('user_types')->where('category', 'talent')->update(['category' => 'job_seeker']);
    }

    private function renameUserType(string $from, string $to): void
    {
        DB::table('user_types')->where('slug', $from)->update(['slug' => $to]);
    }

    private function renameMenu(string $from, string $to, ?string $routeName = null): void
    {
        $values = ['slug' => $to];

        if ($routeName) {
            $values['route_name'] = $routeName;
        }

        DB::table('portal_menus')->where('slug', $from)->update($values);
    }
};

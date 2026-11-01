<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every role (jabatan) as name => label.
     *
     * @var array<string, string>
     */
    private const ROLES = [
        'gm' => 'GM',
        'vgm' => 'VGM',
        'sekretaris' => 'Sekretaris',
        'finance' => 'Finance',
        'head_of_podcast' => 'Head of Podcast',
        'kadiv_announcer' => 'Kadiv Announcer',
        'announcer' => 'Announcer',
        'kadiv_sound_operator' => 'Kadiv Sound Operator',
        'sound_operator' => 'Sound Operator',
        'kadiv_human_resource' => 'Kadiv Human Resources',
        'human_resource' => 'Human Resources',
        'head_of_public_relation' => 'Head of Public Relation',
        'kadiv_partnership' => 'Kadiv Partnership',
        'partnership' => 'Partnership',
        'kadiv_media_relation' => 'Kadiv Media Relation',
        'media_relation' => 'Media Relation',
        'head_of_social_media' => 'Head of Social Media',
        'kadiv_creative' => 'Kadiv Creative',
        'creative' => 'Creative',
        'kadiv_video_editor' => 'Kadiv Video Editor',
        'video_editor' => 'Video Editor',
        'kadiv_design_and_publishing' => 'Kadiv Design and Publishing',
        'design_and_publishing' => 'Design and Publishing',
        'kadiv_web_development' => 'Kadiv Web Development',
        'web_development' => 'Web Development',
    ];

    /**
     * Old users.role text => new roles.name.
     *
     * @var array<string, string>
     */
    private const LEGACY_ROLES = [
        'announcer' => 'announcer',
        'so' => 'sound_operator',
        'hr' => 'human_resource',
    ];

    /**
     * Run the migrations.
     *
     * Unknown role texts are rejected before any schema change, because MySQL DDL is not transactional.
     */
    public function up(): void
    {
        $unknownRoles = DB::table('users')
            ->whereNotIn('role', array_keys(self::LEGACY_ROLES))
            ->distinct()
            ->orderBy('role')
            ->pluck('role')
            ->all();

        if ($unknownRoles !== []) {
            throw new RuntimeException('Role user tidak dikenal: '.implode(', ', $unknownRoles));
        }

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->timestamps();
        });

        $now = now();
        DB::table('roles')->insert(array_map(
            fn (string $name, string $label): array => ['name' => $name, 'label' => $label, 'created_at' => $now, 'updated_at' => $now],
            array_keys(self::ROLES),
            self::ROLES,
        ));

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('password');
        });

        $roleIds = DB::table('roles')->pluck('id', 'name');

        foreach (self::LEGACY_ROLES as $legacyRole => $roleName) {
            DB::table('users')->where('role', $legacyRole)->update(['role_id' => $roleIds[$roleName]]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->restrictOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('role_id');
        });

        foreach (['program_crew', 'session_crew'] as $crewTable) {
            DB::table($crewTable)->where('position', 'so')->update(['position' => 'sound_operator']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * Roles without an old role text are rejected before anything is touched.
     */
    public function down(): void
    {
        $unrestorableRoles = DB::table('users')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->whereNotIn('roles.name', self::LEGACY_ROLES)
            ->distinct()
            ->orderBy('roles.name')
            ->pluck('roles.name')
            ->all();

        if ($unrestorableRoles !== []) {
            throw new RuntimeException('Role tidak bisa dikembalikan: '.implode(', ', $unrestorableRoles));
        }

        foreach (['program_crew', 'session_crew'] as $crewTable) {
            DB::table($crewTable)->where('position', 'sound_operator')->update(['position' => 'so']);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->after('password');
        });

        $roleIds = DB::table('roles')->pluck('id', 'name');

        foreach (self::LEGACY_ROLES as $legacyRole => $roleName) {
            DB::table('users')->where('role_id', $roleIds[$roleName])->update(['role' => $legacyRole]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('roles');
    }
};

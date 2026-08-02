<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{
  foreach(['publication_types','publication_modes'] as $name)Schema::create($name,function(Blueprint $t){$t->id();$t->string('code',40)->unique();$t->string('short_name',80)->nullable();$t->string('display_name',150);$t->text('description')->nullable();$t->unsignedInteger('sort_order')->default(0);$t->boolean('is_active')->default(true)->index();$t->timestamps();$t->softDeletes();});
  Schema::create('candidate_publications',function(Blueprint $t){$t->id();$t->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();$t->foreignId('publication_type_id')->constrained()->restrictOnDelete();$t->foreignId('publication_mode_id')->nullable()->constrained()->nullOnDelete();$t->string('area_of_publication');$t->unsignedInteger('publication_count')->default(1);$t->string('title')->nullable();$t->string('publisher_name')->nullable();$t->date('published_on')->nullable();$t->string('edition_or_volume')->nullable();$t->string('identifier')->nullable();$t->string('publication_url')->nullable();$t->text('co_authors')->nullable();$t->boolean('is_peer_reviewed')->default(false);$t->boolean('is_verified')->default(false);$t->text('description')->nullable();$t->timestamps();});
  $now=now();foreach(['NEWSPAPER_ARTICLE'=>'Newspaper Article','RESEARCH_PAPER'=>'Research Paper','JOURNAL_ARTICLE'=>'Journal Article','MAGAZINE_ARTICLE'=>'Magazine Article','BOOK'=>'Book','BOOK_CHAPTER'=>'Book Chapter','STORY'=>'Story','POEM'=>'Poem / Poetry','BLOG'=>'Blog Post','CONFERENCE_PAPER'=>'Conference Paper','OTHER_PUBLICATION'=>'Other Publication'] as $i=>$name)DB::table('publication_types')->insert(['code'=>$i,'display_name'=>$name,'sort_order'=>DB::table('publication_types')->count()*10+10,'is_active'=>true,'created_at'=>$now,'updated_at'=>$now]);foreach(['ONLINE'=>'Online','PRINT'=>'Print / Offline','BOTH'=>'Online and Print'] as $i=>$name)DB::table('publication_modes')->insert(['code'=>$i,'display_name'=>$name,'sort_order'=>DB::table('publication_modes')->count()*10+10,'is_active'=>true,'created_at'=>$now,'updated_at'=>$now]);
 }
 public function down():void{Schema::dropIfExists('candidate_publications');Schema::dropIfExists('publication_modes');Schema::dropIfExists('publication_types');}
};

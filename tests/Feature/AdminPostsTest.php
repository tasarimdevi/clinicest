<?php

declare(strict_types=1);

use App\Livewire\Admin\PostForm;
use App\Livewire\Admin\Posts;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
});

it('blocks users without content.view from the posts list', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('access-admin');

    $this->actingAs($user)->get(route('admin.posts.index'))->assertForbidden();
});

it('lets an admin view and filter the posts list', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $cost = PostCategory::create(['name' => ['en' => 'Cost'], 'slug' => 'ap-cost', 'sort' => 1]);
    Post::create(['kind' => 'blog', 'category_id' => $cost->id, 'slug' => 'ap-post-a', 'title' => ['en' => 'Post Alpha'], 'body' => ['en' => '<p>a</p>'], 'status' => 'published']);
    Post::create(['kind' => 'guide', 'slug' => 'ap-post-b', 'title' => ['en' => 'Post Beta'], 'body' => ['en' => '<p>b</p>'], 'status' => 'draft']);

    Livewire::actingAs($admin)
        ->test(Posts::class)
        ->assertSee('Post Alpha')
        ->assertSee('Post Beta')
        ->set('kind', 'guide')
        ->assertSee('Post Beta')
        ->assertDontSee('Post Alpha');
});

it('lets a content editor create a draft post but not publish it', function () {
    $editor = User::factory()->create();
    $editor->assignRole('content_editor'); // access-admin + content.view + content.edit, no content.publish

    Livewire::actingAs($editor)
        ->test(PostForm::class)
        ->set('kind', 'blog')
        ->set('title', 'New Post')
        ->set('slug', 'new-post')
        ->set('body', '<p>Body</p>')
        ->call('save');

    $post = Post::where('slug', 'new-post')->first();
    expect($post)->not->toBeNull();
    expect($post->status)->toBe('draft');

    Livewire::actingAs($editor)
        ->test(PostForm::class, ['post' => $post])
        ->call('publish')
        ->assertForbidden();

    expect($post->fresh()->status)->toBe('draft');
});

it('blocks a user without content.edit from creating a post', function () {
    $seoManager = User::factory()->create();
    $seoManager->assignRole('seo_manager'); // access-admin + seo.manage + content.view only

    // Mount-time authorization failures on a full-page Livewire component
    // don't propagate through Livewire::test() as a raw exception — assert
    // against the real route (same pattern as AdminClinicsTest).
    $this->actingAs($seoManager)->get(route('admin.posts.create'))->assertForbidden();
});

it('lets an admin publish and unpublish a post', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $post = Post::create(['kind' => 'blog', 'slug' => 'ap-publish-me', 'title' => ['en' => 'Publish Me'], 'body' => ['en' => '<p>x</p>'], 'status' => 'draft']);

    Livewire::actingAs($admin)
        ->test(PostForm::class, ['post' => $post])
        ->call('publish');

    expect($post->fresh()->status)->toBe('published');
    expect($post->fresh()->published_at)->not->toBeNull();

    Livewire::actingAs($admin)
        ->test(PostForm::class, ['post' => $post])
        ->call('unpublish');

    expect($post->fresh()->status)->toBe('draft');
});

it('lets an admin toggle publish state directly from the posts list', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $post = Post::create(['kind' => 'blog', 'slug' => 'ap-toggle-me', 'title' => ['en' => 'Toggle Me'], 'body' => ['en' => '<p>x</p>'], 'status' => 'draft']);

    Livewire::actingAs($admin)
        ->test(Posts::class)
        ->call('togglePublish', $post->id);

    expect($post->fresh()->status)->toBe('published');
});

it('does not let editing an existing post silently change its published status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $post = Post::create([
        'kind' => 'blog', 'slug' => 'ap-stays-published', 'title' => ['en' => 'Stays Published'],
        'body' => ['en' => '<p>x</p>'], 'status' => 'published', 'published_at' => now(),
    ]);

    Livewire::actingAs($admin)
        ->test(PostForm::class, ['post' => $post])
        ->set('title', 'Stays Published Edited')
        ->call('save');

    expect($post->fresh()->status)->toBe('published');
    expect($post->fresh()->getTranslation('title', 'en'))->toBe('Stays Published Edited');
});

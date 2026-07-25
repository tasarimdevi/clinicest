<?php

declare(strict_types=1);

use App\Models\Faq;
use App\Models\Post;
use App\Models\PostCategory;

it('renders the guide pillar page with a byline and links to cluster articles', function () {
    Post::create([
        'kind' => 'guide', 'is_pillar' => true, 'slug' => 'gb-pillar',
        'title' => ['en' => 'The Complete Guide'], 'body' => ['en' => '<p>Intro.</p>'],
        'author_name' => 'Clinicest Editorial Team', 'status' => 'published', 'published_at' => now(),
    ]);
    $category = PostCategory::create(['name' => ['en' => 'Cost'], 'slug' => 'gb-cost', 'sort' => 1]);
    Post::create([
        'kind' => 'guide', 'category_id' => $category->id, 'slug' => 'gb-cluster',
        'title' => ['en' => 'Cluster Article'], 'body' => ['en' => '<p>Body.</p>'],
        'status' => 'published', 'published_at' => now(),
    ]);

    $this->get(route('guide.index'))
        ->assertOk()
        ->assertSee('The Complete Guide')
        ->assertSee('Clinicest Editorial Team')
        ->assertSee('Cluster Article');
});

it('renders an honest empty state on the guide pillar when no pillar post exists', function () {
    $this->get(route('guide.index'))
        ->assertOk()
        ->assertSee('being written');
});

it('renders a guide cluster article and links back to the pillar', function () {
    Post::create([
        'kind' => 'guide', 'is_pillar' => true, 'slug' => 'gb-pillar-2',
        'title' => ['en' => 'Pillar Two'], 'body' => ['en' => '<p>Intro.</p>'],
        'status' => 'published', 'published_at' => now(),
    ]);
    $article = Post::create([
        'kind' => 'guide', 'slug' => 'gb-article-2',
        'title' => ['en' => 'Article Two'], 'body' => ['en' => '<p>Body.</p>'],
        'status' => 'published', 'published_at' => now(),
    ]);

    $this->get(route('guide.show', $article->slug))
        ->assertOk()
        ->assertSee('Article Two')
        ->assertSee('Pillar Two');
});

it('404s for a draft guide article', function () {
    $article = Post::create([
        'kind' => 'guide', 'slug' => 'gb-draft', 'title' => ['en' => 'Draft Article'],
        'body' => ['en' => '<p>Body.</p>'], 'status' => 'draft',
    ]);

    $this->get(route('guide.show', $article->slug))->assertNotFound();
});

it('404s for a blog post reached via the guide route', function () {
    $post = Post::create([
        'kind' => 'blog', 'slug' => 'gb-blog-not-guide', 'title' => ['en' => 'A Blog Post'],
        'body' => ['en' => '<p>Body.</p>'], 'status' => 'published', 'published_at' => now(),
    ]);

    $this->get(route('guide.show', $post->slug))->assertNotFound();
});

it('renders the blog index with a featured post and category filter', function () {
    $category = PostCategory::create(['name' => ['en' => 'Stories'], 'slug' => 'gb-stories', 'sort' => 1]);
    Post::create([
        'kind' => 'blog', 'category_id' => $category->id, 'slug' => 'gb-old-post',
        'title' => ['en' => 'Old Post'], 'body' => ['en' => '<p>x</p>'],
        'status' => 'published', 'published_at' => now()->subDays(10),
    ]);
    $newest = Post::create([
        'kind' => 'blog', 'category_id' => $category->id, 'slug' => 'gb-new-post',
        'title' => ['en' => 'New Post'], 'body' => ['en' => '<p>x</p>'],
        'status' => 'published', 'published_at' => now(),
    ]);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSee('New Post')
        ->assertSee('Old Post');
});

it('renders a blog post with faqs and related posts in the same category', function () {
    $category = PostCategory::create(['name' => ['en' => 'Stories'], 'slug' => 'gb-stories-2', 'sort' => 1]);
    $post = Post::create([
        'kind' => 'blog', 'category_id' => $category->id, 'slug' => 'gb-main-post',
        'title' => ['en' => 'Main Post'], 'body' => ['en' => '<p>Body.</p>'],
        'status' => 'published', 'published_at' => now(),
    ]);
    Post::create([
        'kind' => 'blog', 'category_id' => $category->id, 'slug' => 'gb-related-post',
        'title' => ['en' => 'Related Post'], 'body' => ['en' => '<p>x</p>'],
        'status' => 'published', 'published_at' => now(),
    ]);
    Faq::create([
        'faqable_type' => Post::class, 'faqable_id' => $post->id,
        'question' => ['en' => 'Is this a real post?'], 'answer' => ['en' => 'Yes.'],
        'sort' => 1, 'status' => 'published',
    ]);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('Main Post')
        ->assertSee('Related Post')
        ->assertSee('Is this a real post?');
});

it('404s for a draft blog post', function () {
    $post = Post::create([
        'kind' => 'blog', 'slug' => 'gb-draft-blog', 'title' => ['en' => 'Draft Blog'],
        'body' => ['en' => '<p>x</p>'], 'status' => 'draft',
    ]);

    $this->get(route('blog.show', $post->slug))->assertNotFound();
});

it('does not show a medically-reviewed-by line when no reviewer was recorded', function () {
    $post = Post::create([
        'kind' => 'blog', 'slug' => 'gb-no-reviewer', 'title' => ['en' => 'No Reviewer Post'],
        'body' => ['en' => '<p>x</p>'], 'status' => 'published', 'published_at' => now(),
    ]);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertDontSee('Medically reviewed by');
});

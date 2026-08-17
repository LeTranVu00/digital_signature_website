<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSiteContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_pricing_site_content_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('admin.site-content.index', ['tab' => 'pricing']))
            ->assertOk()
            ->assertSee('data-pricing-plans', false)
            ->assertSee('data-add-pricing-plan', false)
            ->assertSee('data-toggle-plan-delete', false)
            ->assertSee('showSiteContentToast', false)
            ->assertSee('x-on:submit.prevent="saveContent($event)"', false)
            ->assertDontSee('name="plans[0][price]"', false)
            ->assertSee('Danh dau xoa goi nay khi luu', false)
            ->assertSee('x-bind:disabled="! deletePlan"', false);

        $content = $response->getContent();

        $this->assertDoesNotMatchRegularExpression('/<details(?=[^>]*data-pricing-plan="0")(?=[^>]*\bopen\b)[^>]*>/s', $content);
        $this->assertDoesNotMatchRegularExpression('/<details(?=[^>]*data-pricing-plan="__INDEX__")(?=[^>]*\bopen\b)[^>]*>/s', $content);
        $this->assertStringNotContainsString('node.open = true', $content);
    }

    public function test_admin_can_update_home_youtube_video_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.site-content.index', ['tab' => 'home']))
            ->assertOk()
            ->assertSee('name="youtube_embed_url"', false)
            ->assertSee('name="video_thumbnail"', false)
            ->assertSee('data-add-home-repeater="services"', false)
            ->assertSee('data-blank-home-repeater-template="process_steps"', false)
            ->assertSee('data-blank-home-repeater-template="stats"', false)
            ->assertSee('Chưa có ô giới thiệu nào', false)
            ->assertSee('<details', false);

        $this->actingAs($admin)
            ->patch(route('admin.site-content.update', 'home'), [
                'hero_title' => 'Trang chu',
                'hero_copy' => 'Mo ta hero',
                'intro_text' => 'Noi dung gioi thieu',
                'process_intro' => 'Quy trinh ro rang',
                'youtube_embed_url' => 'https://www.youtube.com/watch?v=UiF2jKUSaQk&list=RDUiF2jKUSaQk&start_radio=1',
                'cta_title' => 'Can ho tro?',
                'cta_copy' => 'Lien he chung toi.',
            ])
            ->assertRedirect(route('admin.site-content.index', ['tab' => 'home']));

        $this->assertSame(
            'https://www.youtube.com/embed/UiF2jKUSaQk',
            SiteSetting::valueFor('home')['youtube_embed_url']
        );

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('https://www.youtube.com/embed/UiF2jKUSaQk', false);
    }

    public function test_admin_can_add_and_delete_dynamic_home_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.site-content.update', 'home'), [
                'hero_title' => 'Trang chu',
                'hero_copy' => 'Mo ta hero',
                'intro_text' => 'Noi dung gioi thieu',
                'services' => [
                    ['title' => 'Giu lai', 'desc' => 'Noi dung giu lai'],
                    ['delete' => '1', 'title' => 'Xoa di', 'desc' => 'Noi dung xoa'],
                ],
                'process_intro' => 'Quy trinh ro rang',
                'process_steps' => [
                    ['title' => 'Buoc giu lai', 'desc' => 'Mo ta buoc'],
                    ['delete' => '1', 'title' => 'Buoc xoa', 'desc' => 'Mo ta xoa'],
                ],
                'stats' => [
                    ['value' => '10+', 'label' => 'Ho so'],
                    ['delete' => '1', 'value' => '99+', 'label' => 'Xoa'],
                ],
                'cta_title' => 'Can ho tro?',
                'cta_copy' => 'Lien he chung toi.',
            ])
            ->assertRedirect(route('admin.site-content.index', ['tab' => 'home']));

        $home = SiteSetting::valueFor('home');

        $this->assertSame([['title' => 'Giu lai', 'desc' => 'Noi dung giu lai']], $home['services']);
        $this->assertSame([['title' => 'Buoc giu lai', 'desc' => 'Mo ta buoc']], $home['process_steps']);
        $this->assertSame([['value' => '10+', 'label' => 'Ho so']], $home['stats']);
    }

    public function test_admin_can_update_home_video_thumbnail(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.site-content.update', 'home'), [
                'hero_title' => 'Trang chu',
                'hero_copy' => 'Mo ta hero',
                'intro_text' => 'Noi dung gioi thieu',
                'process_intro' => 'Quy trinh ro rang',
                'video_thumbnail' => UploadedFile::fake()->image('thumbnail-video.jpg', 1280, 720),
                'cta_title' => 'Can ho tro?',
                'cta_copy' => 'Lien he chung toi.',
            ])
            ->assertRedirect(route('admin.site-content.index', ['tab' => 'home']));

        $thumbnail = SiteSetting::valueFor('home')['video_thumbnail'];

        $this->assertStringStartsWith('home/', $thumbnail);
        Storage::disk('public')->assertExists($thumbnail);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('storage/'.$thumbnail, false);
    }

    public function test_admin_can_open_software_links_as_collapsed_panels(): void
    {
        SiteSetting::query()->create([
            'key' => 'software',
            'value' => [
                'hero_title' => 'Phan mem ho tro',
                'hero_copy' => 'Mo ta phan mem',
                'notice' => 'Thong bao',
                'items' => [
                    [
                        'name' => 'USB Token',
                        'type' => 'Link nha cung cap',
                        'url' => 'https://example.com/token',
                        'desc' => 'Cong cu cai dat',
                    ],
                ],
            ],
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('admin.site-content.index', ['tab' => 'software']))
            ->assertOk()
            ->assertSee('data-software-categories', false)
            ->assertSee('data-add-software-category', false)
            ->assertSee('data-toggle-software-category-delete', false)
            ->assertSee('data-software-items', false)
            ->assertSee('data-add-software-item', false)
            ->assertSee('data-toggle-software-delete', false)
            ->assertSee('<details', false)
            ->assertSee('<summary', false)
            ->assertSee('USB Token')
            ->assertSee('name="categories[0][items][0][url]"', false)
            ->assertSee('data-blank-software-category-template', false)
            ->assertSee('data-blank-software-item-template', false);

        $content = $response->getContent();

        $this->assertDoesNotMatchRegularExpression('/<details(?=[^>]*data-software-category="0")(?=[^>]*\bopen\b)[^>]*>/s', $content);
        $this->assertDoesNotMatchRegularExpression('/<details(?=[^>]*data-software-category="__CATEGORY_INDEX__")(?=[^>]*\bopen\b)[^>]*>/s', $content);
        $this->assertDoesNotMatchRegularExpression('/<details(?=[^>]*data-software-item="0")(?=[^>]*\bopen\b)[^>]*>/s', $content);
        $this->assertDoesNotMatchRegularExpression('/<details(?=[^>]*data-software-item="__INDEX__")(?=[^>]*\bopen\b)[^>]*>/s', $content);
        $this->assertStringNotContainsString('node.open = true', $content);
    }

    public function test_admin_can_delete_and_add_software_links(): void
    {
        SiteSetting::query()->create([
            'key' => 'software',
            'value' => [
                'hero_title' => 'Phan mem ho tro',
                'hero_copy' => 'Mo ta phan mem',
                'notice' => 'Thong bao',
                'categories' => [
                    [
                        'name' => 'Danh muc cu',
                        'desc' => 'Mo ta danh muc',
                        'items' => [
                            [
                                'name' => 'Can xoa',
                                'type' => 'Link cu',
                                'url' => 'https://example.com/remove',
                                'desc' => 'Se bi xoa',
                            ],
                            [
                                'name' => 'Giu lai',
                                'type' => 'Link moi',
                                'url' => 'https://example.com/keep',
                                'desc' => 'Van giu',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Danh muc se xoa',
                        'desc' => 'Bi xoa',
                        'items' => [
                            [
                                'name' => 'Trong danh muc xoa',
                                'type' => 'Link cu',
                                'url' => 'https://example.com/category-remove',
                                'desc' => 'Se bi xoa cung danh muc',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.site-content.update', 'software'), [
                'hero_title' => 'Phan mem ho tro',
                'hero_copy' => 'Mo ta phan mem',
                'notice' => 'Thong bao',
                'categories' => [
                    0 => [
                        'name' => 'Danh muc cu',
                        'desc' => 'Mo ta danh muc da sua',
                        'items' => [
                            0 => [
                                'delete' => '1',
                            ],
                            1 => [
                                'name' => 'Giu lai',
                                'type' => 'Link moi',
                                'url' => 'https://example.com/keep',
                                'desc' => 'Van giu',
                            ],
                            2 => [
                                'name' => 'Moi them',
                                'type' => 'Link tai',
                                'url' => 'https://example.com/new',
                                'desc' => 'Vua them',
                            ],
                        ],
                    ],
                    1 => [
                        'delete' => '1',
                    ],
                    2 => [
                        'name' => 'Danh muc moi',
                        'desc' => 'Mo ta danh muc moi',
                        'items' => [
                            0 => [
                                'name' => 'Phan mem danh muc moi',
                                'type' => 'Link ho tro',
                                'url' => 'https://example.com/new-category-item',
                                'desc' => 'Trong danh muc moi',
                            ],
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('admin.site-content.index', ['tab' => 'software']));

        $software = SiteSetting::valueFor('software');
        $categories = $software['categories'];
        $items = $software['items'];

        $this->assertCount(2, $categories);
        $this->assertSame('Danh muc cu', $categories[0]['name']);
        $this->assertCount(2, $categories[0]['items']);
        $this->assertSame('Giu lai', $categories[0]['items'][0]['name']);
        $this->assertSame('Moi them', $categories[0]['items'][1]['name']);
        $this->assertSame('Danh muc moi', $categories[1]['name']);
        $this->assertSame('Phan mem danh muc moi', $categories[1]['items'][0]['name']);
        $this->assertCount(3, $items);
    }

    public function test_admin_can_update_contact_zalo_qr_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.site-content.index', ['tab' => 'contact']))
            ->assertOk()
            ->assertSee('name="qr_card[label]"', false)
            ->assertSee('name="qr_card[url]"', false)
            ->assertDontSee('qr_cards[0][image]', false);

        $this->actingAs($admin)
            ->patch(route('admin.site-content.update', 'contact'), [
                'hero_title' => 'Lien he',
                'hero_copy' => 'Mo ta lien he',
                'form_title' => 'Gui thong tin',
                'form_copy' => 'Mo ta form',
                'qr_card' => [
                    'label' => 'QR Zalo tu dong',
                    'url' => 'https://zalo.me/0900000000',
                ],
            ])
            ->assertRedirect(route('admin.site-content.index', ['tab' => 'contact']));

        $contact = SiteSetting::valueFor('contact');

        $this->assertSame('QR Zalo tu dong', $contact['qr_card']['label']);
        $this->assertSame('https://zalo.me/0900000000', $contact['qr_card']['url']);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('data-qr-code', false)
            ->assertSee('data-qr-value="https://zalo.me/0900000000"', false)
            ->assertSee('QR Zalo tu dong');
    }

    public function test_frontend_software_page_shows_category_tabs(): void
    {
        SiteSetting::query()->create([
            'key' => 'software',
            'value' => [
                'hero_title' => 'Phan mem ho tro',
                'hero_copy' => 'Mo ta phan mem',
                'notice' => 'Thong bao',
                'categories' => [
                    [
                        'name' => 'Chu ky so',
                        'desc' => 'Nhom chu ky so',
                        'items' => [
                            [
                                'name' => 'USB Token',
                                'type' => 'Link nha cung cap',
                                'url' => 'https://example.com/token',
                                'desc' => 'Cong cu cai dat',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Hoa don dien tu',
                        'desc' => 'Nhom hoa don',
                        'items' => [
                            [
                                'name' => 'Cong cu hoa don',
                                'type' => 'Link ho tro',
                                'url' => 'https://example.com/invoice',
                                'desc' => 'Cong cu ky hoa don',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->get(route('software'))
            ->assertOk()
            ->assertSee('role="tablist"', false)
            ->assertSee('Chu ky so')
            ->assertSee('Hoa don dien tu')
            ->assertSee('USB Token')
            ->assertSee('Cong cu hoa don')
            ->assertSee('activeCategory === 0', false)
            ->assertSee('activeCategory === 1', false);
    }

    public function test_admin_can_update_and_add_pricing_plans(): void
    {
        Storage::fake('public');

        SiteSetting::query()->create([
            'key' => 'pricing',
            'value' => [
                'hero_title' => 'Bang gia cu',
                'hero_copy' => 'Mo ta cu',
                'plans' => [
                    [
                        'name' => 'Goi cu',
                        'desc' => 'Mo ta cu',
                        'features' => ['Tinh nang cu'],
                        'images' => [],
                    ],
                ],
            ],
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.site-content.update', 'pricing'), [
                'hero_title' => 'Bang gia moi',
                'hero_copy' => 'Mo ta moi',
                'plans' => [
                    0 => [
                        'name' => 'Goi da sua',
                        'desc' => 'Mo ta da sua',
                        'features_text' => "Tinh nang A\nTinh nang B",
                    ],
                    1 => [
                        'name' => 'Goi moi',
                        'desc' => 'Mo ta goi moi',
                        'features_text' => 'Tinh nang moi',
                        'new_image_names' => ['Bang gia moi'],
                        'new_images' => [
                            UploadedFile::fake()->image('bang-gia-moi.jpg', 800, 600),
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('admin.site-content.index', ['tab' => 'pricing']));

        $pricing = SiteSetting::valueFor('pricing');

        $this->assertSame('Bang gia moi', $pricing['hero_title']);
        $this->assertCount(2, $pricing['plans']);
        $this->assertSame('Goi da sua', $pricing['plans'][0]['name']);
        $this->assertArrayNotHasKey('price', $pricing['plans'][0]);
        $this->assertSame(['Tinh nang A', 'Tinh nang B'], $pricing['plans'][0]['features']);
        $this->assertSame('Goi moi', $pricing['plans'][1]['name']);
        $this->assertArrayNotHasKey('price', $pricing['plans'][1]);
        $this->assertSame('Bang gia moi', $pricing['plans'][1]['images'][0]['name']);
        Storage::disk('public')->assertExists($pricing['plans'][1]['images'][0]['path']);
    }

    public function test_admin_can_delete_a_pricing_plan(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('pricing-plans/remove-me.jpg', 'image');

        SiteSetting::query()->create([
            'key' => 'pricing',
            'value' => [
                'hero_title' => 'Bang gia',
                'hero_copy' => 'Mo ta bang gia',
                'plans' => [
                    [
                        'name' => 'Goi can xoa',
                        'desc' => 'Se bi xoa',
                        'features' => ['Tinh nang cu'],
                        'images' => [
                            ['path' => 'pricing-plans/remove-me.jpg', 'name' => 'Anh cu'],
                        ],
                    ],
                    [
                        'name' => 'Goi giu lai',
                        'desc' => 'Van con',
                        'features' => ['Tinh nang con lai'],
                        'images' => [],
                    ],
                ],
            ],
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.site-content.update', 'pricing'), [
                'hero_title' => 'Bang gia',
                'hero_copy' => 'Mo ta bang gia',
                'plans' => [
                    0 => [
                        'delete' => '1',
                    ],
                    1 => [
                        'name' => 'Goi giu lai',
                        'desc' => 'Van con',
                        'features_text' => 'Tinh nang con lai',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.site-content.index', ['tab' => 'pricing']));

        $plans = SiteSetting::valueFor('pricing')['plans'];

        $this->assertCount(1, $plans);
        $this->assertSame('Goi giu lai', $plans[0]['name']);
        Storage::disk('public')->assertMissing('pricing-plans/remove-me.jpg');
    }
}

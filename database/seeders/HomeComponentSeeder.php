<?php

namespace Database\Seeders;

use App\Models\HomeComponent;
use Illuminate\Database\Seeder;

class HomeComponentSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        HomeComponent::truncate();

        // 1. Hero Carousel
        HomeComponent::create([
            'type' => 'hero_carousel',
            'order' => 1,
            'active' => true,
            'config' => [
                'slides' => [
                    [
                        'image' => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?q=80&w=1932&auto=format&fit=crop',
                        'alt' => 'Giải pháp hội nghị truyền hình toàn diện',
                        'title' => '',
                        'subtitle' => '',
                        'link' => '',
                        'button_text' => '',
                    ],
                    [
                        'image' => 'https://images.unsplash.com/photo-1551703599-6b3e8379aa8c?q=80&w=2074&auto=format&fit=crop',
                        'alt' => 'Hạ tầng máy chủ mạnh mẽ',
                        'title' => '',
                        'subtitle' => '',
                        'link' => '',
                        'button_text' => '',
                    ],
                    [
                        'image' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=2000&auto=format&fit=crop',
                        'alt' => 'Kết nối không giới hạn',
                        'title' => '',
                        'subtitle' => '',
                        'link' => '',
                        'button_text' => '',
                    ],
                ],
            ],
        ]);

        // 2. Stats
        HomeComponent::create([
            'type' => 'stats',
            'order' => 2,
            'active' => true,
            'config' => [
                'items' => [
                    ['value' => '1,500+', 'label' => 'Khách hàng tin dùng'],
                    ['value' => '25+', 'label' => 'Đối tác chiến lược'],
                    ['value' => '100+', 'label' => 'Sản phẩm chính hãng'],
                    ['value' => '20', 'label' => 'Tỉnh thành phân phối'],
                ],
            ],
        ]);

        // 3. About
        HomeComponent::create([
            'type' => 'about',
            'order' => 3,
            'active' => true,
            'config' => [
                'badge' => 'Về chúng tôi',
                'title' => 'Đối tác công nghệ',
                'subtitle' => 'Chiến lược & Toàn diện',
                'description' => 'ESAT không chỉ phân phối thiết bị mà còn cung cấp hệ sinh thái giải pháp công nghệ, giúp doanh nghiệp tối ưu vận hành và bứt phá.',
                'quote' => 'Chất lượng là nền tảng, nhưng sự hài lòng của khách hàng mới là đích đến cuối cùng.',
                'image' => '',
                'features' => [
                    [
                        'title' => 'Phân Phối Chính Hãng',
                        'description' => 'Thiết bị hội nghị, âm thanh, máy chiếu top đầu thị trường.',
                    ],
                    [
                        'title' => 'Hỗ Trợ Kỹ Thuật 24/7',
                        'description' => 'Đội ngũ chuyên môn cao, bảo hành và hỗ trợ trọn đời.',
                    ],
                    [
                        'title' => 'Giải Pháp Toàn Diện',
                        'description' => 'Tư vấn tích hợp hệ thống tối ưu, không chỉ bán thiết bị.',
                    ],
                    [
                        'title' => 'Tiên Phong Công Nghệ',
                        'description' => 'Luôn cập nhật thiết bị và xu hướng mới nhất thế giới.',
                    ],
                ],
            ],
        ]);

        // 4. Product Categories
        HomeComponent::create([
            'type' => 'product_categories',
            'order' => 4,
            'active' => true,
            'config' => [
                'title' => 'Danh mục sản phẩm',
                'categories' => [
                    [
                        'image' => 'https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?auto=format&fit=crop&w=300&q=80',
                        'name' => 'Hội nghị truyền hình',
                        'description' => '',
                        'link' => '#',
                    ],
                    [
                        'image' => 'https://images.unsplash.com/photo-1590660046028-edc1010d84d4?auto=format&fit=crop&w=300&q=80',
                        'name' => 'Thiết bị âm thanh',
                        'description' => '',
                        'link' => '#',
                    ],
                    [
                        'image' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=300&q=80',
                        'name' => 'Thiết bị tường lửa',
                        'description' => '',
                        'link' => '#',
                    ],
                    [
                        'image' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=300&q=80',
                        'name' => 'Thiết bị trình chiếu',
                        'description' => '',
                        'link' => '#',
                    ],
                    [
                        'image' => 'https://images.unsplash.com/photo-1544197150-b99a580bbcbf?auto=format&fit=crop&w=300&q=80',
                        'name' => 'Thiết bị mạng',
                        'description' => '',
                        'link' => '#',
                    ],
                    [
                        'image' => 'https://images.unsplash.com/photo-1558494949-ef010dbacc31?auto=format&fit=crop&w=300&q=80',
                        'name' => 'Thiết bị máy chủ',
                        'description' => '',
                        'link' => '#',
                    ],
                    [
                        'image' => 'https://images.unsplash.com/photo-1628126235206-5260b9ea6441?auto=format&fit=crop&w=300&q=80',
                        'name' => 'Thiết bị lưu điện',
                        'description' => '',
                        'link' => '#',
                    ],
                    [
                        'image' => 'https://images.unsplash.com/photo-1517430816045-df4b7de8db98?auto=format&fit=crop&w=300&q=80',
                        'name' => 'Màn hình chuyên dụng',
                        'description' => '',
                        'link' => '#',
                    ],
                ],
            ],
        ]);

        // 5. Featured Products
        HomeComponent::create([
            'type' => 'featured_products',
            'order' => 5,
            'active' => true,
            'config' => [
                'title' => 'Sản phẩm nổi bật',
                'subtitle' => '',
                'display_mode' => 'manual',
                'limit' => 8,
                'view_all_link' => '#',
                'products' => [
                    [
                        'image' => 'https://picsum.photos/400/400?random=1',
                        'name' => 'Máy chủ Dell PowerEdge R260',
                        'price' => '25,000,000đ',
                        'link' => '#',
                    ],
                    [
                        'image' => 'https://picsum.photos/400/400?random=2',
                        'name' => 'Bàn xoay kiếng 30cm (HV)',
                        'price' => '298,000đ',
                        'link' => '#',
                    ],
                    [
                        'image' => 'https://picsum.photos/400/400?random=3',
                        'name' => 'Bàn Xoay Gang - Xi bóng',
                        'price' => '356,000đ',
                        'link' => '#',
                    ],
                    [
                        'image' => 'https://picsum.photos/400/400?random=4',
                        'name' => 'Thùng Xốp Cách Nhiệt 5cm',
                        'price' => 'Liên hệ',
                        'link' => '#',
                    ],
                ],
            ],
        ]);

        // 6. Partners
        HomeComponent::create([
            'type' => 'partners',
            'order' => 6,
            'active' => true,
            'config' => [
                'title' => 'Đối tác chiến lược',
                'auto_scroll' => true,
                'partners' => [
                    [
                        'logo' => 'https://picsum.photos/200/100?random=20',
                        'name' => 'Dell',
                        'link' => '#',
                    ],
                    [
                        'logo' => 'https://picsum.photos/200/100?random=21',
                        'name' => 'ADG',
                        'link' => '#',
                    ],
                    [
                        'logo' => 'https://picsum.photos/200/100?random=22',
                        'name' => 'Richs',
                        'link' => '#',
                    ],
                    [
                        'logo' => 'https://picsum.photos/200/100?random=23',
                        'name' => 'Mauri',
                        'link' => '#',
                    ],
                    [
                        'logo' => 'https://picsum.photos/200/100?random=24',
                        'name' => 'VCCI',
                        'link' => '#',
                    ],
                ],
            ],
        ]);

        // 7. News
        HomeComponent::create([
            'type' => 'news',
            'order' => 7,
            'active' => true,
            'config' => [
                'title' => 'Tin tức & Sự kiện',
                'display_mode' => 'manual',
                'limit' => 6,
                'view_all_link' => '#',
                'posts' => [
                    [
                        'image' => 'https://picsum.photos/600/400?random=10',
                        'title' => 'Triển lãm Food & Hotel Việt Nam 2025 – Cơ hội kết nối ngành',
                        'link' => '#',
                    ],
                    [
                        'image' => 'https://picsum.photos/600/400?random=11',
                        'title' => 'Rich\'s và Vũ Phúc Baking đóng góp bánh cưới tập thể',
                        'link' => '#',
                    ],
                    [
                        'image' => 'https://picsum.photos/600/400?random=12',
                        'title' => 'Gặp gỡ, kết nối cung cầu ngành bánh tại Miền Tây',
                        'link' => '#',
                    ],
                ],
            ],
        ]);

        // 8. Footer
        HomeComponent::create([
            'type' => 'footer',
            'order' => 8,
            'active' => true,
            'config' => [
                'company_name' => 'Công Ty TNHH ESAT',
                'address' => 'Số 123, Đường Nguyễn Văn Linh, Quận 7, TP. Hồ Chí Minh',
                'phone' => '1900 6363 40',
                'hotline' => '1900 6363 40',
                'email' => 'contact@esat.vn',
                'copyright' => '© {year} Công Ty TNHH ESAT. Bảo lưu mọi quyền.',
                'social_links' => [
                    ['platform' => 'facebook', 'url' => '#'],
                    ['platform' => 'messenger', 'url' => '#'],
                    ['platform' => 'zalo', 'url' => '#'],
                ],
                'policies' => [
                    ['label' => 'Chính sách & Điều khoản', 'link' => '#'],
                    ['label' => 'Chính sách đổi trả', 'link' => '#'],
                    ['label' => 'Chính sách vận chuyển', 'link' => '#'],
                    ['label' => 'Bảo mật & Quyền riêng tư', 'link' => '#'],
                ],
            ],
        ]);
    }
}

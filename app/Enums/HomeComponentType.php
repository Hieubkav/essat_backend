<?php

namespace App\Enums;

enum HomeComponentType: string
{
    case HeroCarousel = 'hero_carousel';
    case Stats = 'stats';
    case About = 'about';
    case ProductCategories = 'product_categories';
    case FeaturedProducts = 'featured_products';
    case Partners = 'partners';
    case News = 'news';
    case Footer = 'footer';

    public function getLabel(): string
    {
        return match ($this) {
            self::HeroCarousel => 'Hero Carousel - Banner chính',
            self::Stats => 'Stats - Thống kê nổi bật',
            self::About => 'About - Về chúng tôi',
            self::ProductCategories => 'Product Categories - Danh mục sản phẩm',
            self::FeaturedProducts => 'Featured Products - Sản phẩm nổi bật',
            self::Partners => 'Partners - Đối tác chiến lược',
            self::News => 'News - Tin tức sự kiện',
            self::Footer => 'Footer - Chân trang',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::HeroCarousel => 'Slider banner lớn ở đầu trang',
            self::Stats => 'Các chỉ số thống kê (VD: 1500+ khách hàng)',
            self::About => 'Giới thiệu công ty và điểm mạnh',
            self::ProductCategories => 'Lưới danh mục sản phẩm',
            self::FeaturedProducts => 'Sản phẩm nổi bật/yêu thích',
            self::Partners => 'Logo đối tác chiến lược',
            self::News => 'Tin tức và sự kiện mới nhất',
            self::Footer => 'Thông tin chân trang, liên hệ, social links',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::HeroCarousel => 'heroicon-o-photo',
            self::Stats => 'heroicon-o-chart-bar',
            self::About => 'heroicon-o-information-circle',
            self::ProductCategories => 'heroicon-o-squares-2x2',
            self::FeaturedProducts => 'heroicon-o-star',
            self::Partners => 'heroicon-o-building-office',
            self::News => 'heroicon-o-newspaper',
            self::Footer => 'heroicon-o-bars-3-bottom-left',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }
        return $options;
    }
}

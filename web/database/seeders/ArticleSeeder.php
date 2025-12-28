<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Nghệ Thuật Làm Gốm Thủ Công: Hành Trình Của Đất Và Lửa',
                'content' => 'Gốm sứ không chỉ là những vật dụng hàng ngày, mà còn là tâm hồn của nghệ nhân gửi gắm vào từng khối đất. Từ khâu chọn đất, nhào nặn trên bàn xoay cho đến khi nung trong lò ở nhiệt độ hàng nghìn độ C, mỗi sản phẩm gốm là một tác phẩm nghệ thuật độc bản.',
                'image' => 'https://images.unsplash.com/photo-1565191999001-551c187427bb?auto=format&fit=crop&q=80&w=800',
                'published_at' => now()->subDays(30),
            ],
            [
                'title' => 'Cách Chọn Bình Gốm Trang Trí Cho Không Gian Hiện Đại',
                'content' => 'Một chiếc bình gốm phù hợp có thể làm bừng sáng cả căn phòng. Xu hướng hiện nay là sự kết hợp giữa nét mộc mạc của gốm thô và những đường nét tối giản của kiến trúc hiện đại. Hãy cùng khám phá cách phối hợp màu sắc và hình dáng để tạo điểm nhấn cho ngôi nhà của bạn.',
                'image' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&q=80&w=800',
                'published_at' => now()->subDays(25),
            ],
            [
                'title' => 'Gốm Sứ Bát Tràng: Di Sản Ngàn Năm Văn Hóa Việt',
                'content' => 'Làng gốm Bát Tràng với lịch sử lâu đời đã khẳng định được vị thế của mình qua những dòng men quý và hoa văn tinh xảo. Từ men rạn cổ kính đến men ngọc thanh tao, mỗi sản phẩm đều mang đậm hơi thở của làng quê Việt Nam.',
                'image' => 'https://images.unsplash.com/photo-1593166541910-f199347d9595?auto=format&fit=crop&q=80&w=800',
                'published_at' => now()->subDays(20),
            ],
            [
                'title' => 'Trà Đạo Và Nghệ Thuật Thưởng Thức Gốm Sứ',
                'content' => 'Thưởng trà không chỉ là uống nước, mà còn là cảm nhận sự tinh tế từ bộ ấm chén gốm sứ. Một chiếc ấm thoát nhiệt tốt, giữ hương lâu và cảm giác ấm áp khi cầm trên tay sẽ làm trọn vẹn trải nghiệm của người yêu trà.',
                'image' => 'https://images.unsplash.com/photo-1544259217-061078c1df24?auto=format&fit=crop&q=80&w=800',
                'published_at' => now()->subDays(15),
            ],
            [
                'title' => 'Khám Phá Quy Trình Nung Gốm Ở Nhiệt Độ Cao',
                'content' => 'Nhiệt độ đóng vai trò quyết định đến độ bền và màu sắc của gốm. Nung ở nhiệt độ từ 1200 đến 1300 độ C giúp lớp men chảy đều, tạo độ bóng và đảm bảo sản phẩm không còn tạp chất hóa học, an toàn cho người sử dụng.',
                'image' => 'https://images.unsplash.com/photo-1565191998341-7607a898b9e6?auto=format&fit=crop&q=80&w=800',
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Xu Hướng Gốm Sứ Tối Giản Năm 2024',
                'content' => 'Sự đơn giản lên ngôi với các thiết kế gốm không họa tiết, tập trung vào hình khối và chất liệu men lì. Những gam màu trung tính như kem, xám, và nâu đất mang lại cảm giác bình yên và gần gũi với thiên nhiên.',
                'image' => 'https://images.unsplash.com/photo-1578749553375-35071f65682b?auto=format&fit=crop&q=80&w=800',
                'published_at' => now()->subDays(5),
            ],
        ];

        foreach ($articles as $article) {
            Article::create([
                'title' => $article['title'],
                'slug' => Str::slug($article['title']) . '-' . uniqid(),
                'thumbnail' => $article['image'],
                'content' => $article['content'],
                'published_at' => $article['published_at'],
            ]);
        }
    }
}



# Thiết lập trang Về GreenFarm và Liên hệ

Hai trang này dùng Page và block editor có sẵn của WordPress. Theme không tự tạo trang, không dùng ACF và không chứa sẵn địa chỉ, số điện thoại, email hoặc giờ hoạt động.

## Trang Về GreenFarm

1. Vào **Pages → Add New**.
2. Nhập tiêu đề và nội dung tiếng Việt đã được GreenFarm xác nhận.
3. Trong phần **Summary/Excerpt**, nhập một đoạn giới thiệu ngắn. Để trống nếu không cần; theme sẽ không tự lấy nội dung thân bài làm excerpt.
4. Chọn Featured Image và nhập alt text mô tả đúng nội dung ảnh.
5. Trong **Page Attributes → Template**, chọn **GreenFarm About**.
6. Đặt slug chính xác là `about`.
7. Publish và kiểm tra đường dẫn `/about/`.

Gợi ý cấu trúc block:

- H2: Câu chuyện GreenFarm
- Paragraph/Image: nội dung và hình ảnh đã được xác minh
- H2: Phương pháp canh tác
- H3 khi cần chia nhỏ từng phương pháp
- H2: Con người và cộng đồng
- H2: Giá trị GreenFarm theo đuổi

Theme tự hiển thị CTA cuối trang tới `/products/` và `/contact/`. Không thêm một H1 khác trong nội dung; Page title đã là H1.

## Trang Liên hệ

1. Vào **Pages → Add New**.
2. Nhập tiêu đề và phần giới thiệu tiếng Việt.
3. Chọn Featured Image và alt text nếu có ảnh phù hợp.
4. Trong **Page Attributes → Template**, chọn **GreenFarm Contact**.
5. Đặt slug chính xác là `contact`.
6. Publish và kiểm tra đường dẫn `/contact/`.

Gợi ý cấu trúc block:

- H2: Thông tin liên hệ
- Paragraph hoặc List: thông tin liên hệ thực tế đã được GreenFarm xác nhận
- Buttons: liên kết trực tiếp dùng `mailto:` hoặc `tel:` khi phù hợp
- H2: Địa điểm hoặc giờ hoạt động, chỉ khi đã có dữ liệu chính thức
- Paragraph/List: dữ liệu do người quản trị nội dung cung cấp

Theme không tạo contact form, bản đồ, email, số điện thoại, địa chỉ, giờ hoạt động hoặc social profile. Không nhập dữ liệu minh họa lên website thật.

## Quy tắc nội dung và heading

- Mỗi trang chỉ có một H1 do Page title tạo ra.
- Các phần chính trong block editor bắt đầu bằng H2.
- Chỉ dùng H3 cho nội dung con trực tiếp của một H2; không nhảy từ H2 xuống H4.
- Dùng List cho danh sách, Buttons cho hành động và Paragraph cho nội dung thông thường.
- Link phải có nhãn mô tả rõ hành động; không dùng nhãn chung như “Bấm vào đây”.
- Featured Image cần alt text có nghĩa nếu ảnh truyền tải thông tin. Ảnh trang trí có thể dùng alt rỗng.
- Excerpt phải được nhập thủ công nếu muốn hiển thị trong hero.

## Thêm vào navigation

1. Vào **Appearance → Menus**.
2. Chọn menu đang được gán cho **Primary navigation** hoặc **Footer navigation**.
3. Thêm hai Page vừa xuất bản.
4. Giữ nhãn menu ngắn, dễ hiểu và lưu menu.
5. Kiểm tra thứ tự, focus bàn phím và link trên desktop lẫn mobile.

Homepage đã liên kết tới `/about/` và `/contact/`. Sau khi xuất bản, kiểm tra các CTA này mở đúng Page và không bị redirect sang slug khác.

## Chuẩn bị cho đa ngôn ngữ

Giai đoạn hiện tại dùng nội dung tiếng Việt với URL tiếng Anh theo cấu trúc dự án. Khi toàn bộ website triển khai đa ngôn ngữ, dùng cùng một giải pháp cho Page content, menu, slug và theme strings; không tạo bản dịch bằng custom fields hoặc nhân đôi logic trong template.

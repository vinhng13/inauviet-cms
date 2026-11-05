<?php
/**
 * 6 landing theo sản phẩm: sách, catalogue, hộp giấy, phong bì, lì xì, bạt.
 *
 * Nội dung chữ viết lại từ mô tả, quy cách các sản phẩm đã có trên web (nhập từ web cũ của Âu Việt); bố cục theo kiểu
 * trang dịch vụ in (lợi ích → chọn quy cách → mẫu đã in → quy trình → lưu ý file → hỏi đáp). Ảnh, bảng quy cách, thẻ
 * sản phẩm, mẫu đã làm là khối động lấy từ sản phẩm, nên sửa sản phẩm là landing tự cập nhật.
 * Dùng chung nút "Tạo các landing" ở Âu Việt › Landing theo mùa (landing-seed.php).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cấu hình từng landing. Mỗi phần chữ là văn bản thường (được escape khi dựng).
 */
function av_ls_product_landings(): array {
	$file_tips = array(
		'File thiết kế ở hệ màu CMYK; màu RGB trên màn hình sẽ lệch khi in.',
		'Chừa lề bù xén 2–3 mm mỗi cạnh; chữ, logo cách đường cắt ít nhất 3 mm.',
		'Ảnh có độ phân giải 300 dpi ở kích thước in thật; chữ chuyển sang dạng đường nét (outline) hoặc gửi kèm font.',
	);
	return array(
		'sach'     => array(
			'slug'     => 'in-sach',
			'title'    => 'In sách, kỷ yếu, tài liệu theo yêu cầu',
			'season'   => 'Sách · Kỷ yếu · Tài liệu',
			'excerpt'  => 'Âu Việt nhận thiết kế và in sách theo yêu cầu: kỷ yếu, sách tự truyện, văn thơ, tạp chí, cẩm nang, tài liệu nội bộ. In số lượng ít hay nhiều, đóng keo gáy, may chỉ hoặc làm bìa cứng tại xưởng ở Hà Nội.',
			'products' => array( 'in-sach-ky-yeu' ),
			'group'    => 'an-pham-marketing',
			'noun'     => 'sách',
			'why'      => array(
				'title' => 'Âu Việt in những loại sách nào',
				'lead'  => 'Sách lưu lại giá trị tinh thần của cá nhân, tập thể trong những dịp kỷ niệm, thành lập, tổng kết.',
				'items' => array(
					'Kỷ yếu trường lớp, kỷ yếu ngày thành lập doanh nghiệp, cơ quan.',
					'Sách tự truyện, văn, thơ, bút ký, công trình nghiên cứu của cá nhân, tập thể.',
					'Tạp chí, bản tin, cẩm nang, sách giới thiệu doanh nghiệp.',
					'Tài liệu đào tạo, giáo trình, hồ sơ nội bộ.',
				),
			),
			'options'  => array(
				array(
					'title' => 'Chọn kiểu đóng sách',
					'lead'  => 'Kiểu đóng quyết định độ bền, cách mở sách và chi phí.',
					'cards' => array(
						array( 'Đóng ghim giữa', 'Cho cuốn mỏng như bản tin, cẩm nang. Số trang là bội số của 4.' ),
						array( 'Đóng keo gáy', 'Gáy vuông, gọn, hợp với sách, kỷ yếu, tài liệu dày vừa.' ),
						array( 'May chỉ, dán keo', 'Gáy chắc, sách mở phẳng hơn, hợp sách dày và dùng lâu.' ),
						array( 'Bìa cứng', 'Bìa bồi carton, cán màng, hợp kỷ yếu và sách lưu niệm.' ),
					),
				),
			),
			'tips'     => array_merge( $file_tips, array( 'Số trang ruột nên chẵn; đóng ghim cần số trang là bội số của 4.', 'Chưa dàn trang? Gửi nội dung Word và hình ảnh, Âu Việt dàn trang và thiết kế bìa.' ) ),
			'faq'      => array(
				array( 'In số lượng ít có được không?', 'Được. Âu Việt nhận in sách số lượng ít hay nhiều; tuỳ số lượng sẽ tư vấn in kỹ thuật số hoặc in offset để tối ưu chi phí.' ),
				array( 'Chưa có file dàn trang thì làm thế nào?', 'Quý khách gửi nội dung và hình ảnh, đội thiết kế Âu Việt dàn trang, thiết kế bìa và gửi bản duyệt trước khi in.' ),
				array( 'Có được xem bản in thử không?', 'Có. Âu Việt in test màu để Quý khách kiểm tra màu sắc, nội dung trước khi in số lượng lớn.' ),
				array( 'Giá in sách phụ thuộc vào gì?', 'Khổ sách, số trang, loại giấy ruột và bìa, kiểu đóng, gia công bìa và số lượng.' ),
			),
			'quote'    => array( 'Nhận báo giá in sách, kỷ yếu', 'Gửi khổ sách, số trang và số lượng qua Zalo hoặc gọi, Âu Việt tư vấn quy cách và báo giá trong giờ làm việc.' ),
		),
		'catalogue' => array(
			'slug'     => 'in-catalogue',
			'title'    => 'Thiết kế và in catalogue, brochure',
			'season'   => 'Catalogue · Brochure · Hồ sơ năng lực',
			'excerpt'  => 'Catalogue là công cụ bán hàng giúp khách hàng xem sản phẩm mọi lúc, mọi nơi. Âu Việt lo trọn từ biên soạn nội dung, hình ảnh, thiết kế đến in catalogue, tờ gấp trên giấy cao cấp tại xưởng.',
			'products' => array( 'in-catalogue', 'in-to-gap' ),
			'group'    => 'an-pham-marketing',
			'noun'     => 'catalogue',
			'why'      => array(
				'title' => 'Âu Việt làm gì cho cuốn catalogue của bạn',
				'lead'  => 'Một cuốn catalogue hiệu quả cần đầu tư từ nội dung, hình ảnh đến chất lượng in.',
				'items' => array(
					'Biên soạn nội dung theo mục tiêu chiến dịch và đối tượng khách hàng.',
					'Chuẩn bị, xử lý hình ảnh sản phẩm để thu hút ngay từ cái nhìn đầu tiên.',
					'Thiết kế nhất quán với nhận diện thương hiệu, phối hợp chặt chẽ với Quý khách.',
					'In trên giấy cao cấp, máy móc đáp ứng số lượng lớn trong thời gian ngắn.',
				),
			),
			'options'  => array(
				array(
					'title' => 'Chọn quy cách catalogue',
					'lead'  => 'Quy cách thường làm tại Âu Việt; khổ và số trang có thể theo yêu cầu.',
					'cards' => array(
						array( 'Khổ', 'A4, A5, đứng hoặc ngang, hoặc khổ riêng theo yêu cầu.' ),
						array( 'Giấy', 'Ruột Couche 150–200 gsm; bìa Couche hoặc Ivory 250–300 gsm.' ),
						array( 'Đóng quyển', 'Đóng ghim cho cuốn mỏng, keo gáy cho cuốn dày.' ),
						array( 'Gia công bìa', 'Cán màng bóng hoặc mờ, ép kim, phủ UV định hình.' ),
					),
				),
			),
			'products_title' => 'Catalogue và tờ gấp, brochure',
			'tips'     => array_merge( $file_tips, array( 'Số trang catalogue tính cả bìa là bội số của 4.', 'Chưa có nội dung, hình ảnh? Âu Việt hỗ trợ biên soạn và thiết kế trọn gói.' ) ),
			'faq'      => array(
				array( 'In số lượng ít có được không?', 'Được. Số lượng ít Âu Việt in kỹ thuật số, số lượng lớn in offset để giá trên từng cuốn tốt hơn.' ),
				array( 'Chưa có nội dung, hình ảnh thì sao?', 'Đội thiết kế Âu Việt cùng Quý khách xác định mục tiêu, biên soạn nội dung, chọn và xử lý hình ảnh rồi thiết kế.' ),
				array( 'Có in thử trước không?', 'Có. Âu Việt in test màu để duyệt màu sắc, nội dung trước khi in chính thức.' ),
				array( 'Giá in catalogue phụ thuộc vào gì?', 'Khổ, số trang, loại giấy ruột và bìa, kiểu đóng, gia công bìa và số lượng.' ),
			),
			'quote'    => array( 'Nhận báo giá in catalogue', 'Gửi khổ, số trang, số lượng hoặc mẫu tham khảo qua Zalo, Âu Việt tư vấn và báo giá trong giờ làm việc.' ),
		),
		'hop'      => array(
			'slug'     => 'in-hop-giay',
			'title'    => 'In hộp giấy, thùng carton theo yêu cầu',
			'season'   => 'Bao bì · Hộp giấy · Thùng carton',
			'excerpt'  => 'Hộp giấy in logo và hình ảnh thương hiệu giúp bảo vệ hàng hoá, đóng gói gọn và nâng giá trị sản phẩm. Âu Việt in hộp giấy, hộp cứng, thùng carton theo đúng kích thước sản phẩm của Quý khách.',
			'products' => array( 'in-hop-giay', 'in-thung-carton' ),
			'group'    => 'bao-bi',
			'noun'     => 'hộp',
			'why'      => array(
				'title' => 'Vì sao nên in hộp riêng cho sản phẩm',
				'lead'  => 'Hộp không chỉ để đựng: đó là lần đầu khách hàng chạm vào thương hiệu.',
				'items' => array(
					'Chứa đựng, bảo vệ hàng hoá, hạn chế hao hụt khi vận chuyển.',
					'Đóng gói và vận chuyển gọn gàng, đơn giản hơn.',
					'Đồng bộ nhận diện thương hiệu, giúp khách nhận ra sản phẩm ngay.',
					'Gia tăng giá trị sản phẩm, nâng trải nghiệm mở hộp của khách hàng.',
					'Thân thiện môi trường hơn bao bì nhựa.',
				),
			),
			'options'  => array(
				array(
					'title' => 'Chọn loại hộp',
					'lead'  => 'Âu Việt tư vấn loại hộp theo sản phẩm, trọng lượng và ngân sách.',
					'cards' => array(
						array( 'Hộp giấy Ivory, Duplex', 'In 4 màu, bế theo khuôn; hợp mỹ phẩm, thực phẩm, phụ kiện.' ),
						array( 'Hộp cứng bồi carton', 'Cứng cáp, sang trọng; hợp quà tặng, sản phẩm cao cấp.' ),
						array( 'Thùng carton sóng', 'Carton 3 lớp, 5 lớp, in 1–4 màu; dùng đóng hàng, vận chuyển.' ),
						array( 'Gia công', 'Cán màng bóng, mờ; ép kim; phủ UV định hình; bế theo khuôn.' ),
					),
				),
			),
			'products_title' => 'Hộp giấy và thùng carton',
			'tips'     => array(
				'Gửi kích thước lọt lòng (dài × rộng × cao) của sản phẩm cần đựng.',
				'Cho biết trọng lượng hàng để chọn độ dày giấy hoặc số lớp sóng carton.',
				'Âu Việt gửi khuôn bế để thiết kế đúng vị trí nắp, tai gài, đường gấp.',
				'File thiết kế ở hệ màu CMYK, ảnh 300 dpi ở kích thước in thật.',
			),
			'faq'      => array(
				array( 'Hộp có làm theo kích thước riêng không?', 'Có. Hộp giấy và thùng carton đều làm theo kích thước sản phẩm của Quý khách.' ),
				array( 'Nên chọn giấy nào cho hộp?', 'Ivory, Duplex, Couche 250–350 gsm cho hộp giấy; hộp cần cứng cáp thì bồi carton; hàng nặng, vận chuyển xa dùng thùng carton sóng.' ),
				array( 'Có in thử trước khi sản xuất không?', 'Có. Âu Việt in test màu để duyệt màu sắc, nội dung trước khi in số lượng lớn.' ),
				array( 'Giá in hộp phụ thuộc vào gì?', 'Kích thước, loại giấy, số màu in, gia công (cán màng, ép kim, UV), khuôn bế và số lượng.' ),
			),
			'quote'    => array( 'Nhận báo giá in hộp giấy', 'Gửi kích thước, số lượng và mẫu hộp tham khảo qua Zalo hoặc gọi, Âu Việt tư vấn loại hộp và báo giá.' ),
		),
		'phongbi'  => array(
			'slug'     => 'in-phong-bi',
			'title'    => 'In phong bì thư in logo doanh nghiệp',
			'season'   => 'Ấn phẩm văn phòng',
			'excerpt'  => 'Phong bì in logo và thông tin doanh nghiệp giúp thư từ, hồ sơ gửi đối tác chuyên nghiệp hơn và quảng bá thương hiệu mỗi lần trao tay. Âu Việt in phong bì A4, A5, 12 × 22 cm hoặc theo yêu cầu.',
			'products' => array( 'in-phong-bi', 'in-kep-file' ),
			'group'    => 'an-pham-van-phong',
			'noun'     => 'phong bì',
			'why'      => array(
				'title' => 'Vai trò của phong bì in riêng',
				'lead'  => 'Phong bì là thứ đối tác cầm trên tay trước khi đọc nội dung bên trong.',
				'items' => array(
					'Tạo thiện cảm và sự chuyên nghiệp với người nhận.',
					'Quảng bá thương hiệu mỗi khi phong bì chuyển qua nhiều người.',
					'Giữ hồ sơ, tài liệu gọn gàng, kín đáo khi gửi đi.',
					'Đồng bộ với card visit, kẹp file trong bộ nhận diện văn phòng.',
				),
			),
			'options'  => array(
				array(
					'title' => 'Khổ phong bì thông dụng',
					'lead'  => 'Ngoài các khổ dưới đây, Âu Việt làm phong bì theo kích thước riêng.',
					'cards' => array(
						array( 'A4 (25 × 35 cm)', 'Đựng hồ sơ, tài liệu A4 không cần gấp.' ),
						array( 'A5 (17 × 24 cm)', 'Đựng tài liệu gấp đôi, thiệp mời, voucher.' ),
						array( '12 × 22 cm', 'Thư từ, hoá đơn, thư mời gấp ba.' ),
						array( 'Giấy và cách in', 'Giấy Fort, Couche, Kraft 100–150 gsm. Từ 200 chiếc in offset; ít hơn in laser.' ),
					),
				),
			),
			'products_title' => 'Phong bì và kẹp file',
			'tips'     => $file_tips,
			'faq'      => array(
				array( 'In số lượng ít có được không?', 'Được. Dưới 200 chiếc Âu Việt in laser; từ 200 chiếc trở lên in offset, số lượng càng nhiều giá càng tốt.' ),
				array( 'Nên chọn giấy nào?', 'Giấy Fort cho phong bì văn phòng thông dụng; Couche cho màu in tươi; Kraft cho phong cách mộc.' ),
				array( 'Có thiết kế phong bì giúp không?', 'Có. Âu Việt thiết kế theo logo, màu nhận diện và có thể làm đồng bộ với card visit, kẹp file.' ),
				array( 'Giá in phong bì phụ thuộc vào gì?', 'Khổ, loại giấy, số màu in và số lượng.' ),
			),
			'quote'    => array( 'Nhận báo giá in phong bì', 'Gửi khổ, số lượng và logo qua Zalo hoặc gọi, Âu Việt báo giá trong giờ làm việc.' ),
		),
		'lixi'     => array(
			'slug'     => 'in-li-xi',
			'title'    => 'In bao lì xì Tết in logo doanh nghiệp',
			'season'   => 'Quà tặng Tết 2027',
			'excerpt'  => 'Bao lì xì in logo là món quà Tết nhỏ mà ý nghĩa: tri ân đối tác, khách hàng, nhân viên và quảng bá thương hiệu suốt mùa xuân. Âu Việt thiết kế và in bao lì xì 8 × 16 cm hoặc theo yêu cầu, ép kim, dập nổi.',
			'products' => array( 'in-bao-li-xi' ),
			'group'    => 'qua-tang-theo-mua',
			'noun'     => 'bao lì xì',
			'why'      => array(
				'title' => 'Ai nên in bao lì xì riêng',
				'lead'  => 'Lì xì đầu năm là lời chúc may mắn, tài lộc; bao lì xì in logo đưa lời chúc ấy đi cùng thương hiệu.',
				'items' => array(
					'Doanh nghiệp: tri ân, quảng bá thương hiệu tới đối tác, khách hàng, nhân viên, thể hiện văn hoá doanh nghiệp.',
					'Cửa hàng: tặng kèm khi mua hàng hoặc bán lì xì, tăng hiệu quả marketing dịp Tết.',
					'Cá nhân: thể hiện cá tính, kinh doanh thêm hoặc gây quỹ từ thiện.',
				),
			),
			'options'  => array(
				array(
					'title' => 'Gia công làm nổi bật bao lì xì',
					'lead'  => 'Có thể kết hợp nhiều kiểu gia công trên cùng một mẫu.',
					'cards' => array(
						array( 'Cán màng mờ', 'Bề mặt mịn, sang; thường dùng với giấy Couche.' ),
						array( 'Ép kim (ép nhũ)', 'Nhũ vàng, bạc cho logo, chữ chúc Tết.' ),
						array( 'Dập nổi, dập chìm', 'Tạo chiều sâu cho logo, hoạ tiết.' ),
						array( 'Phủ UV điểm', 'Làm bóng chi tiết cần nhấn.' ),
					),
				),
				array(
					'title' => 'Gợi ý thiết kế',
					'lead'  => 'Bao lì xì khá nhỏ nên nội dung cần ngắn gọn, dễ nhìn.',
					'cards' => array(
						array( 'Nội dung', 'Câu chúc Tết kèm logo và sản phẩm tiêu biểu.' ),
						array( 'Màu sắc', 'Theo màu logo hoặc thiên về đỏ, vàng.' ),
						array( 'Hoạ tiết', 'Mai, đào, bánh chưng, ông đồ, cảnh sum vầy.' ),
						array( 'Con giáp', 'Tết 2027 là năm Đinh Mùi, có thể dùng hình con dê.' ),
					),
				),
			),
			'tips'     => $file_tips,
			'faq'      => array(
				array( 'Nên đặt in bao lì xì khi nào?', 'Nên đặt sớm trước Tết để đủ thời gian thiết kế, in thử, gia công và giao hàng.' ),
				array( 'Kích thước bao lì xì thường là bao nhiêu?', 'Thông dụng 8 × 16 cm; Âu Việt làm kích thước khác theo yêu cầu.' ),
				array( 'Dùng giấy gì?', 'Couche, Ivory hoặc giấy mỹ thuật 150–250 gsm.' ),
				array( 'Giá in lì xì phụ thuộc vào gì?', 'Kích thước, loại giấy, gia công (ép kim, dập nổi, UV) và số lượng.' ),
			),
			'quote'    => array( 'Nhận báo giá in bao lì xì', 'Gửi số lượng, logo và mẫu yêu thích qua Zalo hoặc gọi, Âu Việt tư vấn mẫu và báo giá.' ),
		),
		'bat'      => array(
			'slug'     => 'in-bat',
			'title'    => 'In bạt hiflex, backdrop, standee khổ lớn',
			'season'   => 'In khổ lớn · Sự kiện',
			'excerpt'  => 'Băng rôn, banner, backdrop, phướn in bạt hiflex là cách quảng cáo ngoài trời tiết kiệm mà tiếp cận được nhiều người. Âu Việt in bạt mọi kích thước, gia công đục lỗ, xỏ que, bắn khung và lắp đặt backdrop tại sự kiện.',
			'products' => array( 'in-bat-hiflex', 'in-backdrop', 'in-standee' ),
			'group'    => 'in-kho-lon',
			'noun'     => 'bạt',
			'why'      => array(
				'title' => 'In bạt dùng để làm gì',
				'lead'  => 'Bạt hiflex làm từ nhựa PVC, chịu nắng mưa, in phun sắc nét để nhìn rõ từ xa.',
				'items' => array(
					'Băng rôn, banner, khẩu hiệu treo ngoài trời.',
					'Backdrop sân khấu, hội nghị, lễ ký kết, sự kiện.',
					'Phướn, biển hộp có đèn chiếu sáng bên trong.',
					'Standee bạt cho cửa hàng, gian hàng.',
				),
			),
			'options'  => array(
				array(
					'title' => 'Chọn loại bạt',
					'lead'  => 'Bạt dày 0,32 mm được dùng nhiều nhất vì vừa độ dày, hợp in khổ lớn.',
					'cards' => array(
						array( 'Bạt xuyên sáng 100%', 'Dày 0,32–0,34 mm, giá tốt, xuyên sáng tốt; hợp biển hộp có đèn, treo ban đêm đẹp.' ),
						array( 'Bạt xuyên sáng 50%', 'Dày 0,36–0,38 mm, ban ngày rõ nội dung hơn; hợp băng rôn, phông sân khấu.' ),
						array( 'Bạt không xuyên sáng', 'Dày trên 0,46 mm, căng được kích thước lớn ở nơi nhiều gió.' ),
					),
				),
				array(
					'title' => 'Các kiểu gia công bạt',
					'lead'  => 'Chọn theo cách treo và vị trí lắp đặt.',
					'cards' => array(
						array( 'Đục lỗ (đóng khoen)', 'Gấp mép, đục lỗ 4 góc để treo móc; dùng cho băng rôn, khẩu hiệu, standee bạt.' ),
						array( 'Gấp mép xỏ que', 'Để thừa 5 cm hai đầu để xỏ cây treo; hay dùng cho băng rôn quảng cáo.' ),
						array( 'Bắn khung sắt', 'Căng bạt lên khung cho backdrop, biển lớn; Âu Việt lắp đặt tại sự kiện.' ),
					),
				),
			),
			'products_title' => 'Bạt, backdrop, standee',
			'tips'     => array(
				'File thiết kế ở hệ màu CMYK.',
				'Với bạt khổ lớn, ảnh 100–150 dpi ở kích thước thật là đủ vì người xem đứng xa.',
				'Chừa mép cho đục lỗ hoặc xỏ que (khoảng 5 cm mỗi đầu với kiểu xỏ que).',
				'Đo kích thước thực tế tại vị trí treo trước khi thiết kế.',
			),
			'faq'      => array(
				array( 'In được bạt kích thước bao nhiêu?', 'Âu Việt in bạt mọi kích thước; khổ lớn được nối theo kích thước cần.' ),
				array( 'Bạt có bền khi treo ngoài trời không?', 'Có. Bạt hiflex chịu nắng mưa; nơi nhiều gió nên dùng bạt dày, không xuyên sáng hoặc bắn khung.' ),
				array( 'Âu Việt có lắp đặt backdrop không?', 'Có. Âu Việt đóng khung và lắp đặt backdrop tại sự kiện.' ),
				array( 'Giá in bạt phụ thuộc vào gì?', 'Kích thước (mét vuông), loại bạt, kiểu gia công và lắp đặt.' ),
			),
			'quote'    => array( 'Nhận báo giá in bạt, backdrop', 'Gửi kích thước, số lượng và vị trí treo qua Zalo hoặc gọi, Âu Việt tư vấn loại bạt và báo giá.' ),
		),
	);
}

function av_ls_details( string $q, string $a ): array {
	return av_ls_block( 'core/details', array( 'className' => 'av-lfaq' ), '<details class="wp-block-details av-lfaq"><summary>' . esc_html( $q ) . '</summary>', array( av_ls_p( esc_html( $a ) ) ), '</details>' );
}

/**
 * Dựng nội dung một landing sản phẩm.
 */
function av_ls_build_product( array $img, array $c ): array {
	$b   = array();
	$b[] = av_ls_dyn( 'auviet/landing-hero' );
	$b[] = av_ls_badges( array( 'Xưởng in trực tiếp tại Hà Nội từ 2011', 'Thiết kế, tư vấn, in test màu miễn phí', 'Giao hàng tận nơi' ) );

	$b[] = av_ls_section( 'av-lsec--why', array_merge( av_ls_head( '', $c['why']['title'], $c['why']['lead'] ), array( av_ls_list( array_map( 'esc_html', $c['why']['items'] ), 'av-lchecks av-lchecks--cols' ) ) ) );

	foreach ( $c['options'] as $i => $o ) {
		$cards = array();
		foreach ( $o['cards'] as $card ) {
			$cards[] = av_ls_group( array( av_ls_h( $card[0], 3 ), av_ls_p( esc_html( $card[1] ) ) ), 'av-lcard' );
		}
		$b[] = av_ls_section( 'av-lsec--options', array_merge( av_ls_head( '', $o['title'], $o['lead'], 0 === $i ? 'quy-cach' : '' ), array( av_ls_grid( $cards, 'av-lcards', '220px' ) ) ) );
	}

	$b[] = av_ls_section( 'av-lsec--specs', array_merge( av_ls_head( '', 'Quy cách thường làm', 'Bảng tham khảo; khổ, giấy, gia công đều làm được theo yêu cầu riêng.' ), array( av_ls_dyn( 'auviet/landing-specs' ) ) ) );
	$b[] = av_ls_section( 'av-lsec--samples', array_merge( av_ls_head( '', 'Mẫu ' . $c['noun'] . ' Âu Việt đã in', '', 'mau' ), array( av_ls_dyn( 'auviet/landing-samples' ) ) ) );
	if ( count( $c['products'] ) > 1 ) {
		$b[] = av_ls_section( 'av-lsec--products', array_merge( av_ls_head( '', $c['products_title'] ?? 'Sản phẩm' ), array( av_ls_dyn( 'auviet/landing-products' ) ) ) );
	}

	$steps = array(
		array( 'Tư vấn & báo giá', 'Gửi số lượng, quy cách hoặc mẫu tham khảo qua Zalo, điện thoại; Âu Việt tư vấn chất liệu, gia công và báo giá.' ),
		array( 'Thiết kế, duyệt mẫu', 'Đội thiết kế lên mẫu theo nhận diện thương hiệu, chỉnh sửa đến khi Quý khách duyệt.' ),
		array( 'In thử, chốt màu', 'In test màu để kiểm tra màu sắc, nội dung trước khi in số lượng lớn; có thể duyệt màu tại xưởng.' ),
		array( 'Sản xuất, giao hàng', 'In, gia công tại xưởng, kiểm tra thành phẩm, đóng gói và giao tận nơi.' ),
	);
	$li    = array();
	foreach ( $steps as $s ) {
		$li[] = av_ls_block( 'core/list-item', array(), '<li><strong>' . esc_html( $s[0] ) . '</strong>' . esc_html( $s[1] ) . '</li>' );
	}
	$b[] = av_ls_section( 'av-lsec--steps', array_merge( av_ls_head( 'Quy trình 4 bước', 'Đặt in ' . $c['noun'] . ' tại Âu Việt', '', 'quy-trinh' ), array( av_ls_block( 'core/list', array( 'ordered' => true, 'className' => 'av-chain av-lsteps' ), '<ol class="wp-block-list av-chain av-lsteps">', $li, '</ol>' ) ) ) );

	$b[] = av_ls_section( 'av-lsec--tips', array_merge( av_ls_head( '', 'Lưu ý khi chuẩn bị file' ), array( av_ls_list( array_map( 'esc_html', $c['tips'] ), 'av-lchecks' ), av_ls_p( 'Chưa có file? <a href="/thiet-ke/">Âu Việt thiết kế giúp</a> từ logo, nội dung và ý tưởng của Quý khách.' ) ) ) );
	$b[] = av_ls_dyn( 'auviet/landing-projects' );
	$b[] = av_ls_reasons( $img );
	$b[] = av_ls_partners( $img );

	$faq = array();
	foreach ( $c['faq'] as $f ) {
		$faq[] = av_ls_details( $f[0], $f[1] );
	}
	$b[] = av_ls_section( 'av-lsec--faq', array_merge( av_ls_head( '', 'Câu hỏi thường gặp' ), $faq ) );
	$b[] = av_ls_dyn( 'auviet/landing-quote', array( 'title' => $c['quote'][0], 'text' => $c['quote'][1] ) );
	return $b;
}

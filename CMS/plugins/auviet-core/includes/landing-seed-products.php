<?php
/**
 * Landing theo sản phẩm: sách, catalogue, hộp giấy, phong bì, lì xì, in phun khổ lớn, tờ rơi, tờ gấp, hoá đơn,
 * thiệp, sổ tay, tem nhãn, card visit.
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
			'slug'     => 'in-phun-kho-lon',
			'old_slug' => 'in-bat',
			'title'    => 'In phun khổ lớn: bạt, standee, decal, canvas, in lụa',
			'season'   => 'In phun khổ lớn · Sự kiện · Trang trí',
			'excerpt'  => 'Âu Việt in phun kỹ thuật số khổ lớn trên bạt hiflex, decal PP, vải lụa, canvas; làm standee, khung standee, decal dán ô tô và hangtag. Nhận in mọi kích thước, gia công và lắp đặt tại Hà Nội.',
			'products' => array( 'in-bat-hiflex', 'in-standee', 'in-lua-vai', 'in-tranh-canvas', 'in-hangtag', 'in-decal' ),
			'group'    => 'in-kho-lon',
			'noun'     => 'khổ lớn',
			'samples_title' => 'Mẫu in khổ lớn Âu Việt đã làm',
			'steps_title'   => 'Đặt in phun khổ lớn tại Âu Việt',
			'why'      => array(
				'title' => 'Âu Việt in phun khổ lớn những gì',
				'lead'  => 'Máy in phun kỹ thuật số khổ lớn in được trên nhiều chất liệu, đáp ứng mọi kích thước cho quảng cáo, sự kiện và trang trí.',
				'items' => array(
					'In bạt hiflex: băng rôn, banner, phông sân khấu, biển hộp có đèn.',
					'In standee và khung standee: chữ X, cuốn (roll-up), khung sắt, 2 mặt.',
					'In decal PP dán tường, kính, showroom; in decal dán ô tô quảng cáo.',
					'In lụa, in vải silk bằng mực bám tốt, không lem khi gặp nước.',
					'In tranh canvas căng khung treo phòng khách, văn phòng, quán.',
					'In hangtag, thẻ treo cho quần áo, túi xách, quà tặng.',
				),
			),
			'options'  => array(
				array(
					'title' => 'Chọn chất liệu in',
					'lead'  => 'Mỗi chất liệu hợp một vị trí và thời gian sử dụng khác nhau.',
					'cards' => array(
						array( 'Bạt hiflex', 'Nhựa PVC chịu nắng mưa. Dày 0,32 mm dùng nhiều nhất; bạt không xuyên sáng (trên 0,46 mm) cho biển lớn nơi nhiều gió.' ),
						array( 'Decal PP', 'Mặt sau có keo, dán lên tường, kính, xe; cán bóng hoặc cán mờ để bền màu, không bong tróc.' ),
						array( 'Vải lụa, silk', 'In mực gốc dầu, màu sinh động, kháng nước; dùng cho phông, cờ, trang trí sự kiện.' ),
						array( 'Canvas', 'Vải không thấm nước, căng khung phẳng, cuộn không gãy nếp; hợp tranh treo tường.' ),
					),
				),
				array(
					'title' => 'Standee và khung standee',
					'lead'  => 'Đổi nội dung chỉ cần in lại tấm hình, không phải làm lại khung.',
					'cards' => array(
						array( 'Standee chữ X', 'Khung chữ X phía sau, nhẹ, dễ lắp, giá tốt; hợp cửa hàng, khuyến mãi ngắn ngày.' ),
						array( 'Standee cuốn (roll-up)', 'Hộp cuốn bảo vệ tấm in khi cất; kéo lên là dùng, hợp hội nghị, triển lãm.' ),
						array( 'Standee khung sắt, 2 mặt', 'Chắc chắn, đặt lâu dài ở sảnh, showroom; 2 mặt nhìn được từ hai hướng.' ),
					),
				),
				array(
					'title' => 'Gia công và lắp đặt',
					'lead'  => 'Chọn theo cách treo và vị trí đặt.',
					'cards' => array(
						array( 'Đục lỗ, xỏ que', 'Gấp mép đóng khoen 4 góc hoặc chừa 5 cm hai đầu xỏ que; cho băng rôn, banner.' ),
						array( 'Bắn khung, căng khung', 'Căng bạt lên khung sắt cho backdrop, biển lớn; căng canvas lên khung gỗ cho tranh.' ),
						array( 'Cán màng, bế', 'Cán bóng hoặc mờ cho decal; bế theo hình cho decal ô tô, hangtag.' ),
					),
				),
			),
			'products_title' => 'Các sản phẩm in khổ lớn',
			'tips'     => array(
				'File thiết kế ở hệ màu CMYK.',
				'Với bạt, standee nhìn từ xa, ảnh 100–150 dpi ở kích thước thật là đủ; tranh canvas, in lụa nên dùng ảnh 200–300 dpi.',
				'Chừa mép cho đục lỗ, xỏ que hoặc căng khung (khoảng 5 cm mỗi cạnh).',
				'Đo kích thước thực tế tại vị trí treo, dán (kể cả thân xe) trước khi thiết kế.',
			),
			'faq'      => array(
				array( 'In được kích thước bao nhiêu?', 'Âu Việt in mọi kích thước; bạt khổ lớn được nối theo kích thước cần.' ),
				array( 'Decal dán ô tô có bền không?', 'Decal được cán màng để chống phai màu, bong tróc khi đi nắng mưa; Âu Việt tư vấn loại decal theo bề mặt xe.' ),
				array( 'Có bán riêng khung standee không?', 'Có. Âu Việt có khung chữ X, khung cuốn, khung sắt; khung dùng lại được, lần sau chỉ cần in tấm mới.' ),
				array( 'Âu Việt có lắp đặt không?', 'Có. Âu Việt đóng khung, lắp đặt backdrop, biển bạt và dán decal tại chỗ.' ),
				array( 'Giá in khổ lớn phụ thuộc vào gì?', 'Kích thước (mét vuông), chất liệu, kiểu gia công, khung và lắp đặt.' ),
			),
			'quote'    => array( 'Nhận báo giá in phun khổ lớn', 'Gửi kích thước, chất liệu, số lượng và vị trí treo qua Zalo hoặc gọi, Âu Việt tư vấn và báo giá trong giờ làm việc.' ),
			'posts'    => array( 'in-bat-hiflex-ha-noi-gia-tot-in-sac-net', 'in-bat-hiflex-sac-net-ben-mau-gia-canh-tranh', 'in-standee-gia-re-ha-noi-thiet-ke-mien-phi', 'nen-chon-standee-x-hay-standee-cuon-so-sanh-chi-tiet', 'in-decal-pp-theo-kich-thuoc-rieng-toi-uu-chi-phi', 'in-tranh-canvas-ha-noi-dep-sac-net-theo-yeu-cau', 'cac-loai-banner-quang-cao-pho-bien-hien-nay' ),
		),
		'toroi'    => array(
			'slug'     => 'in-to-roi',
			'title'    => 'In tờ rơi quảng cáo theo yêu cầu',
			'season'   => 'Tờ rơi · Tờ bướm · Flyer',
			'excerpt'  => 'Tờ rơi là cách quảng bá chi phí thấp mà tiếp cận trực tiếp được nhiều khách hàng. Âu Việt thiết kế và in tờ rơi khuyến mãi, giới thiệu sản phẩm, khai trương với số lượng từ ít đến lớn, lấy nhanh tại Hà Nội.',
			'products' => array( 'in-to-roi' ),
			'group'    => 'an-pham-marketing',
			'noun'     => 'tờ rơi',
			'why'      => array(
				'title' => 'Vì sao doanh nghiệp vẫn in tờ rơi',
				'lead'  => 'Tờ rơi (tờ bướm) dùng hình ảnh và lời ngắn gọn để đưa thông tin sản phẩm, dịch vụ tới đúng người.',
				'items' => array(
					'Chi phí thấp, hiệu quả quảng bá cao, tiếp cận trực tiếp nhiều khách hàng.',
					'Phát tại cửa hàng, sự kiện, khu dân cư, trường học; kẹp kèm đơn hàng.',
					'Giới thiệu sản phẩm mới, chương trình khuyến mãi, khai trương, showroom mới.',
					'Hỗ trợ đại lý cập nhật thông tin sản phẩm nhanh chóng.',
				),
			),
			'options'  => array(
				array(
					'title' => 'Chọn quy cách tờ rơi',
					'lead'  => 'Quy cách thường làm; khổ và giấy có thể theo yêu cầu.',
					'cards' => array(
						array( 'Khổ', 'A4, A5, A6 hoặc khổ riêng theo yêu cầu.' ),
						array( 'Giấy', 'Couche 100–150 gsm cho tờ phát tay; giấy dày hơn khi cần sang trọng.' ),
						array( 'Số mặt in', 'In 1 mặt tiết kiệm; in 2 mặt chứa nhiều thông tin hơn.' ),
						array( 'Số lượng', 'Ít thì in kỹ thuật số lấy nhanh; nhiều thì in offset để giá mỗi tờ tốt hơn.' ),
					),
				),
			),
			'tips'     => array_merge( $file_tips, array( 'Tiêu đề ngắn, lợi ích rõ, có lời kêu gọi và số điện thoại, Zalo dễ thấy.' ) ),
			'faq'      => array(
				array( 'In tờ rơi số lượng ít được không?', 'Được. Số lượng ít Âu Việt in kỹ thuật số lấy nhanh; số lượng lớn in offset để tối ưu chi phí.' ),
				array( 'Chưa có thiết kế thì sao?', 'Gửi nội dung, logo và hình ảnh, đội thiết kế Âu Việt lên mẫu và chỉnh đến khi Quý khách duyệt.' ),
				array( 'Giá in tờ rơi phụ thuộc vào gì?', 'Khổ, loại giấy, in 1 hay 2 mặt, gia công (cán màng, gấp) và số lượng.' ),
			),
			'quote'    => array( 'Nhận báo giá in tờ rơi', 'Gửi khổ, số lượng hoặc mẫu tham khảo qua Zalo, Âu Việt báo giá trong giờ làm việc.' ),
			'posts'    => array( 'in-to-roi-theo-yeu-cau-thiet-ke-chuyen-nghiep', 'dich-vu-in-to-roi-ha-noi-nhanh-chat-luong-cao', 'nhung-noi-dung-can-co-tren-mot-mau-to-roi', 'nhung-an-pham-quang-cao-doanh-nghiep-nen-co' ),
		),
		'togap'    => array(
			'slug'     => 'in-to-gap',
			'title'    => 'In tờ gấp, brochure theo yêu cầu',
			'season'   => 'Tờ gấp · Brochure · Leaflet',
			'excerpt'  => 'Tờ gấp, brochure giới thiệu doanh nghiệp, sản phẩm gọn trong một tờ giấy gấp 2, gấp 3. Âu Việt thiết kế, in và gia công tờ gấp trên giấy Couche, Ivory, cán màng tại xưởng ở Hà Nội.',
			'products' => array( 'in-to-gap' ),
			'group'    => 'an-pham-marketing',
			'noun'     => 'tờ gấp',
			'why'      => array(
				'title' => 'Tờ gấp dùng vào việc gì',
				'lead'  => 'Nhiều thông tin hơn tờ rơi, gọn và rẻ hơn catalogue.',
				'items' => array(
					'Giới thiệu doanh nghiệp, hồ sơ năng lực rút gọn.',
					'Giới thiệu sản phẩm, bảng giá, thực đơn.',
					'Phát tại hội chợ, hội thảo, quầy lễ tân, showroom.',
					'Hướng dẫn sử dụng, tài liệu đi kèm sản phẩm.',
				),
			),
			'options'  => array(
				array(
					'title' => 'Chọn kiểu gấp và quy cách',
					'lead'  => 'Khổ thường dùng là A4 gấp 3; khổ và kiểu gấp có thể theo yêu cầu.',
					'cards' => array(
						array( 'Gấp đôi', 'Thành 4 mặt; hợp thiệp giới thiệu, thực đơn ngắn.' ),
						array( 'Gấp 3', 'Thành 6 mặt; kiểu phổ biến nhất cho brochure doanh nghiệp.' ),
						array( 'Gấp zíc zắc, gấp cửa sổ', 'Mở ra theo nhịp; hợp nội dung kể theo bước hoặc cần tạo ấn tượng.' ),
						array( 'Giấy, gia công', 'Couche 150–250 gsm; cán màng bóng hoặc mờ, cấn đường gấp để không nứt.' ),
					),
				),
			),
			'tips'     => array_merge( $file_tips, array( 'Chia nội dung theo từng mặt gấp; mặt gấp vào trong hẹp hơn 1–2 mm để gấp khít.' ) ),
			'faq'      => array(
				array( 'Tờ gấp khác catalogue thế nào?', 'Tờ gấp là một tờ gấp lại, gọn và rẻ; catalogue đóng quyển nhiều trang, chứa nhiều sản phẩm hơn.' ),
				array( 'Giấy dày gấp có bị nứt không?', 'Với giấy từ 200 gsm, Âu Việt cấn đường gấp trước để nếp gấp đẹp, không nứt mực.' ),
				array( 'Giá in tờ gấp phụ thuộc vào gì?', 'Khổ, kiểu gấp, loại giấy, gia công và số lượng.' ),
			),
			'quote'    => array( 'Nhận báo giá in tờ gấp', 'Gửi khổ, kiểu gấp, số lượng hoặc mẫu tham khảo qua Zalo, Âu Việt báo giá trong giờ làm việc.' ),
			'posts'    => array( 'quy-trinh-thiet-ke-va-in-to-gap-chuyen-nghiep', 'brochure-va-catalogue-khac-nhau-nhu-the-nao', 'cac-loai-gia-cong-sau-in-pho-bien-hien-nay', 'can-mo-hay-can-bong-nen-chon-loai-nao' ),
		),
		'hoadon'   => array(
			'slug'     => 'in-hoa-don',
			'title'    => 'In hoá đơn bán lẻ, phiếu thu chi, phiếu bảo hành',
			'season'   => 'Hoá đơn · Phiếu thu chi · Biên lai',
			'excerpt'  => 'Âu Việt in hoá đơn bán lẻ, phiếu thu chi, phiếu xuất nhập kho, quyển order, phiếu bảo hành, vé theo mẫu riêng của cửa hàng, doanh nghiệp: 1 đến nhiều liên, đánh số nhảy, đóng quyển.',
			'products' => array( 'in-hoa-don', 'in-phieu-bao-hanh', 'in-ve' ),
			'group'    => 'an-pham-van-phong',
			'noun'     => 'hoá đơn',
			'why'      => array(
				'title' => 'Ai cần in hoá đơn, phiếu riêng',
				'lead'  => 'Hoá đơn bán lẻ không do Bộ Tài chính phát hành, dùng để ghi giao dịch giữa người bán và người mua, giúp kiểm soát thu chi.',
				'items' => array(
					'Quán ăn, nhà hàng, quán cà phê: quyển order, hoá đơn thanh toán.',
					'Shop quần áo, cửa hàng tạp hoá, thực phẩm: hoá đơn bán lẻ.',
					'Doanh nghiệp: phiếu thu, phiếu chi, phiếu xuất nhập kho.',
					'Cửa hàng điện máy, công nghệ: phiếu bảo hành; bãi xe, sự kiện: vé.',
				),
			),
			'options'  => array(
				array(
					'title' => 'Chọn khổ và số liên',
					'lead'  => 'Khổ thường dùng theo từng loại phiếu.',
					'cards' => array(
						array( 'Quyển order', '7 x 10 cm, 7,5 x 10,5 cm; dùng ở nhà hàng, quán.' ),
						array( 'Hoá đơn, phiếu thu chi', 'A6 (10,5 x 15 cm), A5 (15 x 21 cm, dùng nhiều nhất), 19 x 27 cm, A4.' ),
						array( 'Phiếu xuất nhập kho', '19 x 27 cm hoặc A4.' ),
						array( 'Số liên, giấy', '1 liên in giấy Bãi Bằng; 2–4 liên in giấy carbonless tự in sang liên dưới, mỗi liên một màu.' ),
					),
				),
			),
			'products_title' => 'Hoá đơn, phiếu, vé',
			'tips'     => array(
				'Gửi mẫu đang dùng hoặc nội dung cần có: tên cửa hàng, địa chỉ, điện thoại, các cột.',
				'Cho biết số liên, màu từng liên và có cần đánh số nhảy không.',
				'Chọn kiểu đóng: đóng quyển 50 hoặc 100 bộ, có bìa cứng, răng cưa xé.',
			),
			'faq'      => array(
				array( 'Hoá đơn bán lẻ có thay được hoá đơn VAT không?', 'Không. Hoá đơn bán lẻ dùng cho giao dịch, quản lý thu chi nội bộ; hoá đơn VAT theo quy định của cơ quan thuế.' ),
				array( 'Có đánh số thứ tự từng tờ không?', 'Có. Âu Việt đánh số nhảy, mỗi bộ một số để dễ quản lý.' ),
				array( 'Giá in hoá đơn phụ thuộc vào gì?', 'Khổ, số liên, số màu in, đánh số, kiểu đóng quyển và số lượng.' ),
			),
			'quote'    => array( 'Nhận báo giá in hoá đơn', 'Gửi mẫu hoá đơn, số liên và số quyển qua Zalo, Âu Việt báo giá trong giờ làm việc.' ),
			'posts'    => array( 'in-hoa-don-giay-ha-noi-gia-tot-thiet-ke-theo-yeu-cau', 'cac-loai-giay-pho-bien-trong-nganh-in-an' ),
		),
		'thiep'    => array(
			'slug'     => 'in-thiep',
			'title'    => 'Thiết kế và in thiệp các loại lấy nhanh',
			'season'   => 'Thiệp mời · Thiệp chúc mừng · Thiệp cảm ơn',
			'excerpt'  => 'Âu Việt thiết kế và in thiệp mời sự kiện, khai trương, thiệp cưới, sinh nhật, cảm ơn, chúc mừng doanh nghiệp, thiệp Tết, Noel trên giấy Couche, Ivory, giấy mỹ thuật; nhận đơn gấp lấy nhanh tại Hà Nội.',
			'products' => array(),
			'group'    => 'an-pham-marketing',
			'noun'     => 'thiệp',
			'why'      => array(
				'title' => 'Âu Việt in những loại thiệp nào',
				'lead'  => 'Thiệp truyền tải thông điệp trang trọng và tạo ấn tượng tốt với người nhận.',
				'items' => array(
					'Thiệp mời sự kiện, hội nghị, khai trương.',
					'Thiệp cưới, thiệp sinh nhật.',
					'Thiệp cảm ơn, thiệp tri ân khách hàng.',
					'Thiệp chúc mừng doanh nghiệp, thiệp Tết, thiệp Noel.',
				),
			),
			'options'  => array(
				array(
					'title' => 'Chọn giấy và gia công',
					'lead'  => 'Giấy và gia công làm nên vẻ sang trọng của tấm thiệp.',
					'cards' => array(
						array( 'Giấy', 'Couche, Bristol, Ivory cho thiệp thông dụng; giấy mỹ thuật cho thiệp cao cấp.' ),
						array( 'Ép kim, ép nhũ', 'Làm nổi logo, tên sự kiện, hoạ tiết bằng nhũ vàng, bạc.' ),
						array( 'Dập nổi, bế hình', 'Tạo chiều sâu, cắt theo hình hoặc khoét cửa sổ cho thiệp đặc biệt.' ),
						array( 'Phong bì đi kèm', 'In phong bì cùng tông màu để bộ thiệp đồng bộ.' ),
					),
				),
			),
			'tips'     => array_merge( $file_tips, array( 'Kiểm tra kỹ tên người, ngày giờ, địa điểm trước khi duyệt in.' ) ),
			'faq'      => array(
				array( 'Cần thiệp gấp có kịp không?', 'Âu Việt nhận đơn gấp, hỗ trợ từ thiết kế, sửa nội dung đến in và gia công trong thời gian ngắn; Quý khách báo ngày cần để được xếp lịch.' ),
				array( 'Có in tên riêng từng khách mời không?', 'Có. Quý khách gửi danh sách, Âu Việt in tên lên từng thiệp.' ),
				array( 'Giá in thiệp phụ thuộc vào gì?', 'Khổ, loại giấy, gia công (ép kim, dập nổi, bế), phong bì đi kèm và số lượng.' ),
			),
			'quote'    => array( 'Nhận báo giá in thiệp', 'Gửi loại thiệp, số lượng và ngày cần qua Zalo, Âu Việt tư vấn mẫu và báo giá.' ),
			'posts'    => array( 'in-thiet-ke-thiep-cac-loai-lay-ngay-gia-tot-tai-ha-noi', 'cac-loai-gia-cong-sau-in-pho-bien-hien-nay', 'cac-loai-giay-pho-bien-trong-nganh-in-an' ),
		),
		'sotay'    => array(
			'slug'     => 'in-so-tay',
			'title'    => 'In sổ tay, sổ bìa da in logo quà tặng doanh nghiệp',
			'season'   => 'Sổ tay · Sổ bìa da · Quà tặng',
			'excerpt'  => 'Sổ tay in logo là quà tặng dùng lâu, giúp thương hiệu hiện diện hằng ngày. Âu Việt làm sổ bìa da thật, da PU, simili, ruột in offset theo yêu cầu, logo dập chìm hoặc ép kim.',
			'products' => array( 'so-bia-da' ),
			'group'    => 'an-pham-van-phong',
			'noun'     => 'sổ tay',
			'why'      => array(
				'title' => 'Vì sao sổ tay là quà tặng phù hợp',
				'lead'  => 'Sổ tay đồng hành cùng người nhận trong công việc hằng ngày, khác những món quà chỉ dùng một lần.',
				'items' => array(
					'Thể hiện sự quan tâm tới khách hàng, đối tác, nhân viên.',
					'Logo, màu sắc, thông tin liên hệ giúp thương hiệu được nhớ tới.',
					'Dùng cho hội nghị, sự kiện, tri ân, kỷ niệm thành lập.',
					'Thiết kế riêng theo hình ảnh của từng thương hiệu.',
				),
			),
			'options'  => array(
				array(
					'title' => 'Chọn bìa sổ',
					'lead'  => 'Mỗi loại bìa có ưu điểm và mức giá khác nhau.',
					'cards' => array(
						array( 'Da thật', 'Sang trọng nhất, mềm, bền; cho quà tặng cao cấp.' ),
						array( 'Da PU', 'Bền, giá hợp lý hơn da thật mà vẫn lịch sự.' ),
						array( 'Simili', 'Giá thấp nhất, hợp đơn hàng lớn mà vẫn đẹp.' ),
						array( 'Bìa giấy cứng', 'In màu toàn bộ bìa theo thiết kế, cán màng.' ),
					),
				),
				array(
					'title' => 'Ruột sổ và in logo',
					'lead'  => 'Ruột in offset 1 hoặc nhiều màu theo yêu cầu.',
					'cards' => array(
						array( 'Ruột sổ', 'Giấy 70–100 gsm: kẻ dòng, ô ly, trơn hoặc in trang riêng của doanh nghiệp.' ),
						array( 'Dập chìm', 'Logo ép chìm xuống mặt da, nổi bật mà tinh tế; hợp da PU, da thật.' ),
						array( 'Ép kim, in UV', 'Logo nhũ vàng, bạc hoặc in màu trực tiếp lên bìa.' ),
					),
				),
			),
			'tips'     => array(
				'Gửi logo dạng vector (AI, PDF, SVG) để dập, ép kim sắc nét.',
				'Cho biết khổ sổ, số trang, kiểu ruột và số lượng.',
				'Nên xem mẫu da, mẫu ruột trước khi chốt đơn số lượng lớn.',
			),
			'faq'      => array(
				array( 'Đặt số lượng ít có được không?', 'Được. Âu Việt tư vấn chất liệu bìa và cách in logo phù hợp số lượng để tối ưu chi phí.' ),
				array( 'In được trang riêng trong ruột sổ không?', 'Được, như trang giới thiệu công ty, lịch năm, trang thông tin cá nhân.' ),
				array( 'Giá sổ phụ thuộc vào gì?', 'Loại bìa, khổ, số trang, cách in ruột, cách in logo và số lượng.' ),
			),
			'quote'    => array( 'Nhận báo giá in sổ tay', 'Gửi khổ sổ, loại bìa và số lượng qua Zalo, Âu Việt tư vấn và báo giá.' ),
			'posts'    => array( 'in-so-tay-qua-tang-doanh-nghiep-theo-yeu-cau', 'vi-sao-nhieu-truong-hoc-lua-chon-in-vo-rieng-cho-hoc-sinh' ),
		),
		'tem'      => array(
			'slug'     => 'in-tem-nhan',
			'title'    => 'In tem nhãn, decal, tem mác sản phẩm',
			'season'   => 'Tem nhãn · Decal · Tem mác',
			'excerpt'  => 'Hàng hoá lưu thông phải có nhãn mác. Âu Việt in tem nhãn decal giấy, decal nhựa, decal trong, tem mác quần áo và hangtag, bế theo hình, cán màng, giúp sản phẩm rõ thông tin và dễ nhận diện.',
			'products' => array( 'in-tem-nhan', 'in-decal', 'in-hangtag' ),
			'group'    => 'an-pham-marketing',
			'noun'     => 'tem nhãn',
			'why'      => array(
				'title' => 'Vì sao sản phẩm cần tem nhãn',
				'lead'  => 'Qua nhãn hàng hoá, người mua nhận biết và chọn sản phẩm; nhà sản xuất khẳng định thương hiệu.',
				'items' => array(
					'Đáp ứng quy định ghi nhãn hàng hoá khi lưu thông.',
					'Cung cấp thông tin: thành phần, hạn dùng, xuất xứ, cách dùng.',
					'Tăng nhận diện thương hiệu, phân biệt hàng thật với hàng nhái.',
					'Làm sản phẩm chuyên nghiệp, dễ bán hơn.',
				),
			),
			'options'  => array(
				array(
					'title' => 'Chọn chất liệu tem',
					'lead'  => 'Chọn theo bề mặt dán và môi trường sử dụng.',
					'cards' => array(
						array( 'Decal giấy', 'Giá hợp lý, in đẹp; cho thực phẩm khô, mỹ phẩm, văn phòng phẩm. Không hợp nơi ẩm.' ),
						array( 'Decal nhựa PP, PVC', 'Chống nước, chống rách; cho mỹ phẩm, đồ uống, hàng đông lạnh.' ),
						array( 'Decal trong', 'Không che bao bì, tạo cảm giác tinh tế, hiện đại.' ),
						array( 'Tem mác, hangtag', 'Giấy dày hoặc vải; treo, may vào quần áo, túi xách.' ),
					),
				),
			),
			'products_title' => 'Tem nhãn, decal, hangtag',
			'tips'     => array_merge( $file_tips, array( 'Cho biết kích thước tem, hình dạng bế (tròn, vuông, theo hình) và bề mặt sẽ dán.' ) ),
			'faq'      => array(
				array( 'Tem có bế theo hình được không?', 'Được. Âu Việt bế tem tròn, oval, theo viền logo, giao dạng tờ hoặc cuộn.' ),
				array( 'Tem dán chai lọ để tủ lạnh có bong không?', 'Nên dùng decal nhựa PP hoặc PVC, chống nước, không bong khi gặp ẩm.' ),
				array( 'Giá in tem nhãn phụ thuộc vào gì?', 'Kích thước, chất liệu decal, cán màng, bế, ép kim và số lượng.' ),
			),
			'quote'    => array( 'Nhận báo giá in tem nhãn', 'Gửi kích thước, chất liệu, số lượng hoặc mẫu tem qua Zalo, Âu Việt báo giá trong giờ làm việc.' ),
			'posts'    => array( 'cac-loai-tem-decal-pho-bien-hien-nay', 'in-tem-mac-quan-ao-gia-re-ha-noi-bao-gia-nhanh', 'in-decal-pp-theo-kich-thuoc-rieng-toi-uu-chi-phi' ),
		),
		'card'     => array(
			'slug'     => 'in-card-visit',
			'title'    => 'In card visit, danh thiếp lấy nhanh',
			'season'   => 'Card visit · Danh thiếp · Name card',
			'excerpt'  => 'Card visit nhỏ gọn giúp đối tác nắm nhanh thông tin của bạn. Âu Việt thiết kế và in card visit khổ 9 x 5,5 cm trên giấy Couche, Ốp, giấy mỹ thuật; cán màng, ép kim, bo góc, lấy nhanh tại Hà Nội.',
			'products' => array( 'in-card-visit' ),
			'group'    => 'an-pham-van-phong',
			'noun'     => 'card visit',
			'why'      => array(
				'title' => 'Card visit vẫn quan trọng',
				'lead'  => 'Card visit (name card, danh thiếp) là ấn tượng đầu tiên khi gặp đối tác.',
				'items' => array(
					'Trao đổi thông tin nhanh, chuyên nghiệp trong buổi gặp.',
					'Thể hiện nhận diện thương hiệu đồng bộ của doanh nghiệp.',
					'Có thể thêm mã QR dẫn tới website, Zalo, hồ sơ năng lực.',
				),
			),
			'options'  => array(
				array(
					'title' => 'Chọn khổ, giấy và gia công',
					'lead'  => 'Giấy dày 250–300 gsm để card cứng cáp.',
					'cards' => array(
						array( 'Khổ', '9 x 5,5 cm hoặc 9 x 5 cm (thông dụng), hoặc khổ riêng theo yêu cầu.' ),
						array( 'Giấy Couche', 'Mặt láng mịn, in sắc nét, giá hợp lý; viết bút dễ nhoè.' ),
						array( 'Giấy Ốp', 'Viết, ký không nhoè, bền; độ bóng thấp hơn Couche.' ),
						array( 'Giấy mỹ thuật', 'Hiệu ứng mặt giấy đẹp, gây ấn tượng mạnh; chi phí cao hơn.' ),
					),
				),
			),
			'tips'     => array_merge( $file_tips, array( 'Kiểm tra kỹ số điện thoại, email, chức danh trước khi duyệt in.' ) ),
			'faq'      => array(
				array( 'In card visit mất bao lâu?', 'Card in kỹ thuật số lấy nhanh; card có gia công đặc biệt (ép kim, dập nổi) cần thêm thời gian, Âu Việt báo khi nhận đơn.' ),
				array( 'In cho nhiều người trong công ty được không?', 'Được. Dùng chung một mẫu, Âu Việt thay tên, chức danh, số điện thoại cho từng người.' ),
				array( 'Giá in card visit phụ thuộc vào gì?', 'Loại giấy, in 1 hay 2 mặt, gia công (cán màng, ép kim, bo góc) và số hộp.' ),
			),
			'quote'    => array( 'Nhận báo giá in card visit', 'Gửi số hộp, loại giấy hoặc mẫu card qua Zalo, Âu Việt báo giá trong giờ làm việc.' ),
			'posts'    => array( 'card-visit-co-con-quan-trong-trong-thoi-dai-so', 'cac-loai-giay-pho-bien-trong-nganh-in-an', 'can-mo-hay-can-bong-nen-chon-loai-nao' ),
		),
	);
}

/**
 * Bài viết đã đăng theo đường dẫn, bỏ qua bài chưa có.
 *
 * @return WP_Post[]
 */
function av_ls_posts( array $slugs ): array {
	$out = array();
	foreach ( $slugs as $slug ) {
		$p = get_page_by_path( $slug, OBJECT, 'post' );
		if ( $p && 'publish' === $p->post_status ) {
			$out[] = $p;
		}
	}
	return $out;
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

	if ( $c['products'] ) {
		$b[] = av_ls_section( 'av-lsec--specs', array_merge( av_ls_head( '', 'Quy cách thường làm', 'Bảng tham khảo; khổ, giấy, gia công đều làm được theo yêu cầu riêng.' ), array( av_ls_dyn( 'auviet/landing-specs' ) ) ) );
		$b[] = av_ls_section( 'av-lsec--samples', array_merge( av_ls_head( '', $c['samples_title'] ?? ( 'Mẫu ' . $c['noun'] . ' Âu Việt đã in' ), '', 'mau' ), array( av_ls_dyn( 'auviet/landing-samples' ) ) ) );
	}
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
	$b[] = av_ls_section( 'av-lsec--steps', array_merge( av_ls_head( 'Quy trình 4 bước', $c['steps_title'] ?? ( 'Đặt in ' . $c['noun'] . ' tại Âu Việt' ), '', 'quy-trinh' ), array( av_ls_block( 'core/list', array( 'ordered' => true, 'className' => 'av-chain av-lsteps' ), '<ol class="wp-block-list av-chain av-lsteps">', $li, '</ol>' ) ) ) );

	$b[] = av_ls_section( 'av-lsec--tips', array_merge( av_ls_head( '', 'Lưu ý khi chuẩn bị file' ), array( av_ls_list( array_map( 'esc_html', $c['tips'] ), 'av-lchecks' ), av_ls_p( 'Chưa có file? <a href="/thiet-ke/">Âu Việt thiết kế giúp</a> từ logo, nội dung và ý tưởng của Quý khách.' ) ) ) );
	$b[] = av_ls_dyn( 'auviet/landing-projects' );
	$b[] = av_ls_reasons( $img );
	$b[] = av_ls_partners( $img );

	$faq = array();
	foreach ( $c['faq'] as $f ) {
		$faq[] = av_ls_details( $f[0], $f[1] );
	}
	$b[] = av_ls_section( 'av-lsec--faq', array_merge( av_ls_head( '', 'Câu hỏi thường gặp' ), $faq ) );
	$posts = av_ls_posts( $c['posts'] ?? array() );
	if ( $posts ) {
		$li = array();
		foreach ( $posts as $p ) {
			$li[] = sprintf( '<a href="%s">%s</a>', esc_url( wp_make_link_relative( (string) get_permalink( $p ) ) ), esc_html( get_the_title( $p ) ) );
		}
		$b[] = av_ls_section( 'av-lsec--posts', array_merge( av_ls_head( 'Kiến thức in ấn', 'Bài viết liên quan' ), array( av_ls_list( $li, 'av-lposts' ) ) ) );
	}
	$b[] = av_ls_dyn( 'auviet/landing-quote', array( 'title' => $c['quote'][0], 'text' => $c['quote'][1] ) );
	return $b;
}

USE QL_MYNGHE;

INSERT INTO DANHMUCSANPHAM(TENDANHMUC) VALUES
-- 1
('Chất liệu'),
-- 2
('Lồng bàn'),
-- 3
('Giỏ đựng đồ'),
-- 4
('Giỏ quà'),
-- 5
('Đèn'),
-- 6
('Sản phẩm khác');

INSERT INTO LOAI(MALOAI, TENLOAI, MADANHMUC) VALUES
('L001', 'Mây', 1),
('L002', 'Tre', 1),
('L003', 'Cói', 1),
('L004', 'Guột', 1),
('L005', 'Lục bình', 1),

('L006', 'Lồng bàn lưới', 2),
('L007', 'Lồng bàn truyền thống', 2),

('L008', 'Giỏ to', 3),
('L009', 'Giỏ nhỏ', 3),
('L0010', 'Giỏ picnic', 3),

('L0011', 'Giỏ hoa quả', 4),
('L0012', 'Giỏ tết & trung thu', 4),

('L0013', 'Đèn trang trí', 5),

('L0014', 'Lồng thú cưng', 6),
('L0015', 'Túi xách', 6);

-- MANHACUNGCAP tự động tăng
INSERT INTO NHACUNGCAP(TENNHACUNGCAP, DTHOAI, DIACHI) VALUES
-- 1
('Công ty TNHH Mây Tre Đan Việt', '0987654321', 'Xã Phú Túc, Huyện Phú Xuyên, Hà Nội'),
-- 2
('Hợp tác xã Mây Tre Tân Hòa', '0978345678', 'Xã Tân Hòa, Huyện Chợ Mới, An Giang'),
-- 3
('Công ty TNHH Thủ Công Mỹ Nghệ Tre Việt', '0965123456', 'Xã Tân Thành, Huyện Bình Tân, Vĩnh Long'),
-- 4
('Cơ sở sản xuất Cói Nga Sơn', '0934567890', 'Huyện Nga Sơn, Tỉnh Thanh Hóa'),
-- 5
('Hợp tác xã Lục Bình Nam Định', '0912345678', 'Xã Xuân Kiên, Huyện Xuân Trường, Nam Định'),
-- 6
('Công ty TNHH Guột Việt', '0981234567', 'Huyện Trảng Bàng, Tỉnh Tây Ninh'),
-- 7
('Công ty TNHH Mây Xanh', '0908765432', 'Xã Hòa Bình, Huyện Hòa Bình, Bạc Liêu'),
-- 8
('Hợp tác xã Thủ Công Mỹ Nghệ Bình Định', '0923456789', 'Xã Nhơn Hòa, Huyện An Nhơn, Bình Định'),
-- 9
('Công ty TNHH Tre Tân Phú', '0932123456', 'Xã Tân Phú, Huyện Tân Châu, Tây Ninh'),
-- 10
('Công ty TNHH Thủ Công Mỹ Nghệ Sơn Đồng', '0919876543', 'Xã Sơn Đồng, Huyện Hoài Đức, Hà Nội'),
-- 11
('Hợp tác xã Đan Lục Bình Bến Tre', '0983456789', 'Xã Hưng Lễ, Huyện Giồng Trôm, Bến Tre'),
-- 12
('Hợp tác xã Thổ Cẩm Chăm Ninh Thuận', '0915678432', 'Xã Phước Dinh, Huyện Thuận Nam, Ninh Thuận');


INSERT INTO SANPHAM(MASANPHAM, TENSANPHAM, HINHANH, GIANHAP, GIABAN, SOLUONGTON, MOTA, MALOAI, MAKHUYENMAI, MANHACUNGCAP) VALUES
-- MÂY
-- 5
('SP001', 'Giỏ chữ nhật nhỏ', 'giochunhatnho.jpg',CAST(100000 AS Decimal(18, 0)), CAST(134000 AS Decimal(18, 0)), 50,
'Giỏ cói đựng đồ chữ nhật size nhỏ được đan từ sợi cói với thiết kế đơn giản, nhỏ xinh, được ứng dụng để đựng những món đồ nhỏ nhắn như chìa khóa xe, chìa khóa nhà, các món phụ kiện nhỏ như vòng, khuyên tai hay điều khiển TV, điều khiển điều hòa, giúp cho căn phòng gọn gàng, tinh tế hơn ',
'L001', NULL, 1),
('SP002', 'Khay đũa thìa', 'khayduathia.jpg',CAST(100000 AS Decimal(18, 0)), CAST(120000 AS Decimal(18, 0)), 30, 
'Sản phẩm được làm 100% từ ván tre ép, mà chất liệu tre là một chất liệu bền, ít bị hư hỏng trong quá trình sử dụng. Sản phẩm được làm bán thủ công do đôi bàn tay Việt Nam thực hiện nên sản phẩm được làm một cách tỉ mỉ với những mối nối góc đầy tinh tế. Bề mặt sản phẩm còn được sơn hai lớp giúp chống mối, mốc ngay cả khi tiếp xúc với nước.',
'L001', NULL, 2),
('SP003', 'Giỏ 4 ngăn', 'gio4ngan.jpg', CAST(210000 AS Decimal(18, 0)), CAST(250000 AS Decimal(18, 0)), 40, 
'Giỏ 4 ngăn có kích thước 18x18x13cm, thích hợp để đựng thìa, đũa và các vật dụng nhỏ khác. Sản phẩm được làm thủ công với chất liệu mây, mang lại vẻ đẹp mộc mạc và độ bền cao. Đây là hàng thủ công xuất khẩu.',
'L001', NULL, 7),
('SP004', 'Giỏ Picnic Oval', 'giopicnicoval.jpg',CAST(300000 AS Decimal(18, 0)), CAST(360000 AS Decimal(18, 0)), 50,
'Giỏ Picnic Oval với kích thước 46x31xH19(37cm) là lựa chọn lý tưởng cho các buổi dã ngoại và picnic. Thiết kế hình oval mang đến sự tiện lợi và phong cách, phù hợp để đựng thức ăn và đồ uống.',
'L001', NULL, 1),
('SP005', 'Hộp giấy ăn', 'hopgiayan.jpg',CAST(120000 AS Decimal(18, 0)), CAST(180000 AS Decimal(18, 0)), 40,
'Hộp giấy ăn được chế tác từ các nghệ nhân làng nghề truyền thống, với thiết kế tinh xảo và hiện đại. Kích thước 27x14xh9cm, sản phẩm được xử lý chống mối mọt và chống mốc, phù hợp với không gian xanh và tự nhiên.',
'L001', NULL, 7),

-- TRE
-- 5
('SP006', 'Lồng bàn truyền thống', 'longbantruyenthong.jpg',CAST(130000 AS Decimal(18, 0)), CAST(185000 AS Decimal(18, 0)), 20,
'Lồng bàn truyền thống có đường kính 60cm và 65cm, được làm từ tre tự nhiên, giúp bảo vệ thực phẩm và tạo ra không khí truyền thống. Được thiết kế để sử dụng trong các bữa ăn gia đình hoặc lễ hội.',
'L002', NULL, 9),
('SP007', 'Lồng bàn có khay', 'longbancokhay.jpg',CAST(150000 AS Decimal(18, 0)), CAST(200000 AS Decimal(18, 0)), 35,
'Lồng bàn có khay với đường kính 33cm, mang đến sự tiện lợi trong việc bày trí thực phẩm. Sản phẩm làm từ tre tự nhiên, có thiết kế đẹp mắt và dễ dàng vệ sinh, phù hợp cho các bữa tiệc hoặc sự kiện.',
'L002', NULL, 1),
('SP008', 'Bát tre có đế', 'battre.jpg',CAST(30000 AS Decimal(18, 0)), CAST(50000 AS Decimal(18, 0)), 45,
 'Bát tre có đế làm từ tre tự nhiên với keo đá giúp tạo khuôn chắc chắn. Bề mặt được sơn lớp mỏng hệ nước, đảm bảo an toàn thực phẩm và dễ vệ sinh. Thích hợp cho các món ăn truyền thống.',
 'L002', NULL, 9),
('SP009', 'Bình tre cuốn', 'binhtrecuon.jpg',CAST(100000 AS Decimal(18, 0)), CAST(150000 AS Decimal(18, 0)), 15,
 'Bình tre cuốn làm từ tre tự nhiên, với lớp sơn mỏng hệ nước bảo vệ bề mặt. Sản phẩm mang đến vẻ đẹp truyền thống và tính năng bảo quản tốt. Được sản xuất tại Việt Nam với sự chăm sóc tỉ mỉ.',
'L002', NULL, 1),
 ('SP010', 'Khay tre có tay cầm', 'khaytrecotaycam.jpg',CAST(70000 AS Decimal(18, 0)), CAST(100000 AS Decimal(18, 0)), 7,
 'Khay tre cuốn hình chữ nhật có tay nắm tiện lợi, được làm từ tre mang lại cảm giác gần gũi với tự nhiên. Sản phẩm thích hợp cho việc trình bày món ăn và đồ uống theo kiểu truyền thống và châu Á.',
 'L002', NULL, 2),

 -- CÓI
 -- 5
('SP011', 'Dép cói', 'depcoi.jpg',CAST(40000 AS Decimal(18, 0)), CAST(70000 AS Decimal(18, 0)), 18,
'Dép cói thiết kế đơn giản, nhẹ nhàng, rất phù hợp cho mùa hè hoặc các ngày thời tiết ấm áp. Được làm từ sợi cói tự nhiên, sản phẩm mang lại sự thoải mái và vẻ ngoài thời trang.',
'L003', NULL, 4),
('SP012', 'Hộp đèn điện trang trí nội thất', 'hopdendien.jpg',CAST(10000 AS Decimal(18, 0)), CAST(20000 AS Decimal(18, 0)), 30,
'Hộp đèn điện trang trí nội thất có khung đèn làm từ cói, tạo ra vẻ đẹp mộc mạc và ấm cúng. Phù hợp cho việc trang trí phòng khách hoặc các không gian sống, mang lại ánh sáng nhẹ nhàng và tinh tế.',
'L003', NULL, 4),
('SP013', 'Hộp đựng đồ nữ', 'hopdungdonu.jpg',CAST(110000 AS Decimal(18, 0)), CAST(158000 AS Decimal(18, 0)), 25,
'Hộp đựng đồ nữ với thiết kế đơn giản nhưng tinh tế, bề mặt dệt từ sợi cói tạo nên vẻ đẹp mộc mạc và sang trọng. Bên trong lót bằng vải mềm hoặc nhung, lý tưởng để bảo vệ trang sức như dây chuyền, bông tai và nhẫn.',
'L003', NULL, 4),
('SP014', 'Túi xách nữ', 'tuixachnu.jpg',CAST(240000 AS Decimal(18, 0)), CAST(285000 AS Decimal(18, 0)), 42,
'Túi xách nữ có kiểu dáng đơn giản, nhẹ nhàng và tự nhiên. Được làm từ cói, sản phẩm rất thích hợp cho mùa hè, mang lại sự thoải mái và phong cách thanh lịch.',
'L003', NULL, 4),
('SP015', 'Vỏ trang trí nậm rượu', 'votrangtrinamruou.jpg',CAST(100000 AS Decimal(18, 0)), CAST(147000 AS Decimal(18, 0)), 23,
'Vỏ trang trí nậm rượu bằng cói là lớp bọc bảo vệ nậm rượu, được thiết kế để tạo ra vẻ đẹp mộc mạc và tinh tế. Sản phẩm mang đến sự độc đáo và bảo vệ cho nậm rượu.',
'L003', NULL, 4),

 -- GUỘT
 -- 2
('SP016', 'Hộp giấy guột cao cấp', 'hopgiayguotcaocap.jpg',CAST(300000 AS Decimal(18, 0)), CAST(350000 AS Decimal(18, 0)), 24,
'Hộp giấy Guột cao cấp với kích thước 23x15xH10.5cm, được thiết kế riêng biệt với hộp giấy thông dụng của Việt Nam. Chất liệu Guột mang lại sự sang trọng và độ bền cao, phù hợp để đựng giấy ăn hoặc các vật phẩm khác.',
'L004', NULL, 6),
('SP017', 'Giỏ guột tròn có nắp', 'gioguottronconap.jpg',CAST(200000 AS Decimal(18, 0)), CAST(250000 AS Decimal(18, 0)), 28, 
'Giỏ ruột tròn có nắp được làm từ mây tre đan guột, với thiết kế tỉ mỉ và đẹp mắt. Vẻ đẹp mộc mạc và tự nhiên của sản phẩm phù hợp cho việc lưu trữ và trang trí, tạo điểm nhấn cho không gian sống.',
'L004', NULL, 6),

-- LỤC BÌNH
-- 5
('SP018', 'Bàn đôn lục bình phối cói đan Decor', 'donlucbinh.jpg',CAST(300000 AS Decimal(18, 0)), CAST(350000 AS Decimal(18, 0)), 10,
'Một chiếc bàn đơn lục bình được làm từ các loại cói đẹp mắt, phù hợp cho việc trang trí không gian sống, tăng thêm điểm nhấn tự nhiên và độc đáo cho ngôi nhà.', 
'L005', NULL, 5),
('SP019', 'Giỏ lục bình chứa đồ đường kính 35cm cao 40cm', 'giolucbinh.jpg',CAST(370000 AS Decimal(18, 0)), CAST(420000 AS Decimal(18, 0)), 5,
'Giỏ lục bình được thiết kế tỉ mỹ, phù hợp cho việc chứa đồ gia dụng, tăng thêm vẻ đẹp tự nhiên cho ngôi nhà của bạn. Vật liệu cói lựa chọn giúp sản phẩm bền bỉ và độc đáo.', 
'L005', NULL, 11),
('SP020', 'Thảm lục bình hình chữ nhật decor 90 x 150cm', 'thamlucbinhhinhchunhat.jpg',CAST(600000 AS Decimal(18, 0)), CAST(679000 AS Decimal(18, 0)), 8,
'Thảm lục bình hình chữ nhật được đan từng chi tiết tắc kỳ, phù hợp để trang trí phòng khách, phòng ngủ hoặc không gian sống khác. Kích thước 90 x 150cm lý tưởng để đặt dưới bàn trà.', 
'L005', NULL, 5),
('SP021', 'Thảm lục bình hình tròn decor loại 1 đường kính 1m8', 'thamlucbinhhinhtron.jpg',CAST(600000 AS Decimal(18, 0)), CAST(679000 AS Decimal(18, 0)), 12,
'Thảm lục bình hình tròn được làm từ cói tự nhiên, đường kính 1m8, thích hợp để đặt trong phòng khách, tạo cảm giác âm cúống và gần gũi với thiên nhiên. Thảm phù hợp với nhiều loại nội thất khác nhau.', 
'L005', NULL, 11),
('SP022', 'Giỏ lục bình đựng chậu cây decor đường kính 35cm cao 30cm' , 'giolucbinhdungchaucay.jpg',CAST(200000 AS Decimal(18, 0)), CAST(250000 AS Decimal(18, 0)), 20,
'Giỏ lục bình đựng chậu cây trang trí được làm từ cói chất lượng cao, mang đến vẻ đẹp tự nhiên và độc đáo cho chậu cây của bạn. Sản phẩm này lý tưởng cho các không gian xanh trong nhà.', 
'L005', NULL, 5),

-- LỒNG BÀN LƯỚI
-- 3
('SP023', 'Lồng bàn lưới 2 lớp', 'longbanluoi2lop.jpg',CAST(1200000 AS Decimal(18, 0)), CAST(1400000 AS Decimal(18, 0)), 28, 
'Lồng bàn lưới 2 lớp được thiết kế đặc biệt với hai lớp bảo vệ, giúp ngăn côn trùng xâm nhập và đảm bảo an toàn cho thực phẩm. Sản phẩm được làm từ tre tự nhiên, nhẹ nhàng và dễ dàng vệ sinh, phù hợp cho việc sử dụng hàng ngày.', 
'L006', NULL, 8),
('SP024', 'Lồng bàn lưới chữ nhật', 'longbanluoichunhat.jpg',CAST(1200000 AS Decimal(18, 0)), CAST(1300000 AS Decimal(18, 0)), 32, 
'Lồng bàn lưới chữ nhật được thiết kế để bảo vệ thực phẩm khỏi côn trùng, với hình dáng chữ nhật tiện lợi. Sản phẩm thích hợp để sử dụng trong các buổi tiệc hoặc bữa ăn gia đình, mang lại sự tiện nghi và an toàn.', 
'L006', NULL, 10),
('SP025', 'Lồng bàn lưới tròn', 'longbanluoitron.jpg',CAST(1100000 AS Decimal(18, 0)), CAST(1200000 AS Decimal(18, 0)), 45, 
'Lồng bàn lưới tròn có thiết kế đẹp mắt và tiện dụng, giúp bảo vệ thức ăn khỏi côn trùng một cách hiệu quả. Sản phẩm được làm từ tre và lưới tự nhiên, thân thiện với môi trường và dễ dàng sử dụng.', 
'L006', NULL, 12),

-- LỒNG BÀN TRUYỀN THỐNG
-- 5
('SP026', 'Lồng bàn núm gỗ', 'longbannumgo.jpg',CAST(1300000 AS Decimal(18, 0)), CAST(1500000 AS Decimal(18, 0)), 30, 
'Lồng bàn nứa gỗ được làm từ nứa tự nhiên, với thiết kế đơn giản nhưng sang trọng, giúp bảo vệ thức ăn một cách hiệu quả. Sản phẩm có độ bền cao và dễ dàng vệ sinh, phù hợp cho việc sử dụng trong gia đình hoặc nhà hàng.', 
'L007', NULL, 8),
('SP027', 'Lồng bàn truyền thống', 'longbantruyenthong.jpg',CAST(1500000 AS Decimal(18, 0)), CAST(1700000 AS Decimal(18, 0)), 40, 
'Lồng bàn truyền thống được đan từ tre tự nhiên với các hoa văn cổ điển, mang lại vẻ đẹp mộc mạc và gần gũi. Sản phẩm giúp bảo quản thực phẩm an toàn và tạo điểm nhấn cho không gian bàn ăn của bạn.', 
'L007', NULL, 10),
('SP028', 'Lồng bàn vuông', 'longbanvuong.jpg',CAST(1400000 AS Decimal(18, 0)), CAST(1600000 AS Decimal(18, 0)), 35, 
'Lồng bàn vuông được thiết kế độc đáo, phù hợp để bảo vệ thực phẩm trong các buổi tiệc hoặc bữa ăn gia đình. Sản phẩm được làm từ tre tự nhiên, bền đẹp và thân thiện với môi trường.', 
'L007', NULL, 12),
('SP029', 'Lồng bàn xanh họa tiết', 'longbanxanh.jpg',CAST(1700000 AS Decimal(18, 0)), CAST(1900000 AS Decimal(18, 0)), 25, 
'Lồng bàn xanh họa tiết được thiết kế với các hoa văn tinh xảo, mang lại vẻ đẹp hiện đại và nổi bật. Sản phẩm không chỉ bảo vệ thức ăn mà còn là vật trang trí độc đáo cho bàn ăn của bạn.', 
'L007', NULL, 8),
('SP030', 'Lồng bàn đỏ họa tiết', 'longbando.jpg',CAST(1800000 AS Decimal(18, 0)), CAST(2000000 AS Decimal(18, 0)), 20, 
'Lồng bàn đỏ họa tiết với màu sắc rực rỡ và hoa văn đặc sắc, mang lại không gian bàn ăn sinh động và hấp dẫn. Sản phẩm được làm từ tre tự nhiên, dễ dàng vệ sinh và sử dụng.', 
'L007', NULL, 12),

-- GIỎ TO
-- 4
('SP031', 'Thùng giặt vuông đan lát', 'thunggiatvuongdanlat.jpg',CAST(500000 AS Decimal(18, 0)), CAST(550000 AS Decimal(18, 0)), 14,
'Thùng giặt vuông đan lát trong hình được làm từ chất liệu tự nhiên như tre hoặc mây, với các đường đan chắc chắn. Thiết kế dạng vuông với viền và tay cầm màu đen tạo sự cứng cáp, tiện lợi cho việc di chuyển. Bộ sản phẩm có nhiều kích thước khác nhau, phù hợp để chứa quần áo, đồ dùng trong gia đình. 
Với thiết kế mộc mạc và thân thiện với môi trường, sản phẩm này không chỉ giúp giữ cho không gian gọn gàng mà còn tạo điểm nhấn thẩm mỹ.',
'L008', NULL, 8),
('SP032', 'Thùng giặt tròn đan lát', 'thunggiattrondanlat.jpg',CAST(400000 AS Decimal(18, 0)), CAST(450000 AS Decimal(18, 0)), 25,
'Thùng giặt tròn đan lát trong hình được làm từ chất liệu tre hoặc mây đan, có thiết kế dạng hình trụ với nắp đậy tiện lợi. Phần viền miệng thùng được bọc vải, giúp tăng độ bền và tạo nét thẩm mỹ. Sản phẩm thích hợp để đựng quần áo bẩn, chăn màn hoặc các đồ dùng trong gia đình, mang lại sự gọn gàng và sạch sẽ cho không gian sống. 
Thiết kế tự nhiên, mộc mạc và thân thiện với môi trường, rất phù hợp cho các ngôi nhà có phong cách gần gũi với thiên nhiên.',
'L008', NULL, 10),
('SP033', 'Giỏ to lục bình', 'giotolucbinh.jpg',CAST(360000 AS Decimal(18, 0)), CAST(420000 AS Decimal(18, 0)), 13,
'Giỏ lục bình to trong hình được làm từ chất liệu lục bình đan tay chắc chắn. Thiết kế dạng hình trụ với các kích cỡ khác nhau, có tay cầm tiện lợi để di chuyển. Các sản phẩm này thích hợp để đựng đồ dùng gia đình, quần áo, hoặc làm giỏ trang trí trong không gian sống. Với màu sắc tự nhiên và thiết kế mộc mạc, 
giỏ lục bình mang lại cảm giác gần gũi với thiên nhiên và góp phần trang trí không gian một cách tinh tế và thân thiện với môi trường.',
'L008', NULL, 5),
('SP034', 'Giỏ đựng đồ decor', 'giodungdodecor.jpg',CAST(300000 AS Decimal(18, 0)), CAST(370000 AS Decimal(18, 0)), 21,
'Giỏ đựng đồ decor trong hình được làm từ chất liệu tự nhiên như cỏ hoặc mây, với các đường đan chắc chắn và xen kẽ các màu sắc tạo điểm nhấn.
Thiết kế nhỏ gọn, có tay cầm hai bên giúp dễ dàng di chuyển. Sản phẩm này không chỉ phù hợp để đựng đồ gia đình như quần áo, đồ chơi mà còn dùng để trang trí, tạo thêm nét mộc mạc và tinh tế cho không gian sống. Giỏ decor là lựa chọn lý tưởng cho những ai yêu thích sự tối giản và gần gũi với thiên nhiên.',
'L008', NULL, 12),

-- GIỎ NHỎ
-- 6
('SP035', 'Túi mây năng động thanh lịch', 'tuimaynangdongthanhlich.jpg',CAST(500000 AS Decimal(18, 0)), CAST(580000 AS Decimal(18, 0)), 20,
'Túi mây trong hình có thiết kế nhỏ gọn, được đan từ chất liệu mây tự nhiên, mang lại vẻ thanh lịch và nhẹ nhàng. Phần viền túi và dây đeo được làm từ da hoặc vải, tạo thêm sự chắc chắn và tinh tế. Với phong cách tối giản, túi mây này phù hợp cho các buổi dạo phố, đi chơi hay sự kiện, giúp người dùng tỏa sáng với vẻ ngoài thời trang mà vẫn mộc mạc,
gần gũi với thiên nhiên.',
'L009', NULL, 7),
('SP036', 'Túi lục bình đeo chéo', 'tuilucbinhdeocheo.jpg',CAST(400000 AS Decimal(18, 0)), CAST(470000 AS Decimal(18, 0)), 21,
'Túi lục bình đeo chéo trong hình được làm từ chất liệu lục bình tự nhiên, có thiết kế hình chữ nhật với các đường đan tỉ mỉ. Túi có thêm ngăn nhỏ tiện dụng, dây đeo bằng vải trắng tạo sự thoải mái và phong cách thời trang. Phần khóa kéo chắc chắn, kết hợp với màu sắc trang nhã, phù hợp cho các buổi dạo phố hoặc đi chơi.
Sản phẩm mang lại sự thanh lịch, mộc mạc nhưng vẫn rất hiện đại và tiện lợi.',
'L009', NULL, 5),
('SP037', 'Túi đan tay thời trang', 'tuidantaythoitrang.jpg',CAST(300000 AS Decimal(18, 0)), CAST(380000 AS Decimal(18, 0)), 16,
'Túi đan tay thời trang trong hình được làm từ chất liệu tự nhiên với các đường đan tinh xảo. Thiết kế nhỏ gọn, có tay cầm và dây đeo chéo, đi kèm với phụ kiện nơ ren và trang trí hoa nổi bật, mang lại vẻ nữ tính và đáng yêu. Sản phẩm phù hợp cho các buổi dạo phố hoặc đi chơi, tạo điểm nhấn phong cách vintage và mộc mạc nhưng vẫn rất thời thượng. 
Đây là lựa chọn lý tưởng cho những ai yêu thích phong cách thời trang thủ công và gần gũi với thiên nhiên.',
'L009', NULL, 1),
('SP038', 'Túi cói tròn đeo chéo ', 'tuicoitrondeocheotinhte.jpg',CAST(400000 AS Decimal(18, 0)), CAST(480000 AS Decimal(18, 0)), 19,
'Túi cói tròn đeo chéo trong hình được đan từ chất liệu cói tự nhiên, có thiết kế hình tròn độc đáo và tinh tế. Dây đeo dài bằng chất liệu da nâu mềm mại, tạo sự thoải mái khi đeo. Sản phẩm mang phong cách vintage, phù hợp cho các buổi dạo phố hoặc đi chơi. Túi cói tròn không chỉ mang lại sự tiện lợi mà còn là phụ kiện thời trang ấn tượng, 
tạo điểm nhấn thanh lịch và gần gũi với thiên nhiên.',
'L009', NULL, 4),
('SP039', 'Túi cói mini dây xoắn thời trang ', 'tuicoiminidayxoanthoitrang.jpg',CAST(600000 AS Decimal(18, 0)), CAST(680000 AS Decimal(18, 0)), 23,
'Túi cói mini dây xoắn trong hình có thiết kế nhỏ gọn và tinh tế. Túi được làm từ chất liệu cói tự nhiên, kết hợp với khóa kim loại và dây đeo dạng xoắn vàng sang trọng. Sản phẩm phù hợp để đeo chéo hoặc đeo vai, thích hợp cho các buổi tiệc nhẹ hoặc dạo phố. Với kiểu dáng thanh lịch, 
túi cói mini không chỉ là phụ kiện thời trang ấn tượng mà còn mang lại sự tiện lợi cho người sử dụng.',
'L009', NULL, 4),
('SP040', 'Giỏ bán nguyệt size nhỏ ', 'giobannguyensizenho.jpg',CAST(180000 AS Decimal(18, 0)), CAST(210000 AS Decimal(18, 0)), 22,
'Túi bán nguyệt size nhỏ trong hình được làm từ chất liệu tự nhiên với kiểu dáng hình bán nguyệt độc đáo. Kích thước nhỏ gọn, túi có quai cầm chắc chắn, tạo cảm giác nhẹ nhàng và thanh lịch. Sản phẩm phù hợp cho các buổi dạo phố hoặc sự kiện, giúp tôn lên vẻ ngoài nữ tính, tinh tế của người sử dụng. 
Đây là lựa chọn lý tưởng cho những ai yêu thích phong cách thời trang mộc mạc và gần gũi với thiên nhiên.',
'L009', NULL, 12),

-- GIỎ PICNIC
-- 4
('SP041', 'Giỏ picnic liễu gai', 'giopicniclieugai.jpg',CAST(1500000 AS Decimal(18, 0)), CAST(1800000 AS Decimal(18, 0)), 25, 
'Giỏ picnic liễu gai với thiết kế rộng rãi và bền bỉ, phù hợp cho các buổi dã ngoại hoặc đi chơi cuối tuần. Giỏ được làm từ chất liệu tự nhiên, thân thiện với môi trường và có lớp lót bên trong, giúp bảo quản thực phẩm sạch sẽ và an toàn.', 
'L0010', NULL, 8),
('SP042', 'Set giỏ picnic', 'setgiopicnic.jpg',CAST(1900000 AS Decimal(18, 0)), CAST(2200000 AS Decimal(18, 0)), 20, 
'Set giỏ picnic đa năng bao gồm nhiều phụ kiện, giúp bạn dễ dàng chuẩn bị cho các buổi dã ngoại. Sản phẩm được làm từ chất liệu tre tự nhiên, thân thiện với môi trường và mang lại vẻ đẹp mộc mạc, giản dị.', 
'L0010', NULL, 10),
('SP043', 'Giỏ picnic dạng cao', 'giopicnicdangcao.jpg',CAST(1800000 AS Decimal(18, 0)), CAST(2000000 AS Decimal(18, 0)), 30, 
'Giỏ picnic dạng cao với thiết kế chắc chắn và tiện lợi, giúp bạn dễ dàng mang theo đồ dùng cho các chuyến đi chơi. Sản phẩm được làm từ chất liệu tự nhiên, bền đẹp và rất thích hợp cho các buổi dã ngoại.', 
'L0010', NULL, 12),
('SP044', 'Giỏ picnic lót vải', 'giopicniclotvai.jpg',CAST(1500000 AS Decimal(18, 0)), CAST(1700000 AS Decimal(18, 0)), 28, 
'Giỏ picnic lót vải với lớp lót mềm mại và sang trọng, mang lại sự tiện nghi và bảo vệ tốt cho thực phẩm. Giỏ được làm từ tre tự nhiên, giúp giữ cho không gian bên trong luôn sạch sẽ và an toàn.', 
'L0010', NULL, 10),

-- GIỎ TRÁI CÂY
-- 3
('SP045', 'Giỏ tam giác đựng trái cây', 'giotamgiacdungtraicay.jpg', CAST(900000 AS Decimal(18, 0)), CAST(1100000 AS Decimal(18, 0)), 50, 
'Giỏ tre tam giác được thiết kế để đựng trái cây, mang lại sự mới mẻ và độc đáo cho không gian bếp của bạn. Sản phẩm được làm từ tre tự nhiên, thân thiện với môi trường và có thể sử dụng trong các buổi tiệc hoặc dùng làm quà tặng.', 
'L0011', NULL, 8),
('SP046', 'Giỏ có bằng đựng trái cây', 'giocobangdungtraicay.jpg',CAST(800000 AS Decimal(18, 0)), CAST(1000000 AS Decimal(18, 0)), 40, 
'Giỏ tre có bằng đựng trái cây được đan thủ công từ tre tự nhiên, với thiết kế đơn giản nhưng không kém phần tinh tế. Sản phẩm phù hợp để sử dụng trong gia đình hoặc các buổi gặp mặt, giúp bày biện trái cây một cách đẹp mắt và gọn gàng.', 
'L0011', NULL, 12),
('SP047', 'Giỏ tre quai xách bán nguyệt', 'giotrequaixachbannguyet.jpg', CAST(1300000 AS Decimal(18, 0)),CAST(1500000 AS Decimal(18, 0)), 35, 
'Giỏ tre quai xách bán nguyệt được thiết kế với quai xách tiện lợi, phù hợp để đựng trái cây hoặc các vật dụng nhỏ trong gia đình. Sản phẩm mang lại vẻ đẹp mộc mạc, giản dị và rất thân thiện với môi trường.', 
'L0011', NULL, 10),

-- GIỎ TẾT VÀ TRUNG THU
-- 5
('SP048', 'Giỏ tre màu đỏ quý phái', 'giotremaudoquyphai.jpg',CAST(1600000 AS Decimal(18, 0)), CAST(1800000 AS Decimal(18, 0)), 50, 
'Giỏ tre được làm từ nguyên liệu tự nhiên với màu đỏ quý phái, mang lại vẻ đẹp nổi bật và sang trọng. Giỏ tre này được thiết kế đặc biệt với các chi tiết tỉ mỉ, thể hiện sự khéo léo và tinh tế của người nghệ nhân. Với màu đỏ quyến rũ, sản phẩm không chỉ mang lại sự may mắn mà còn giúp tôn lên vẻ đẹp cho không gian sống hoặc làm quà tặng vào các dịp lễ Tết. Đây là lựa chọn hoàn hảo để thể hiện tình cảm và sự trân trọng đối với người nhận.', 
'L0012', NULL, 9),
('SP049', 'Giỏ tre lục bình sum vầy', 'giotlucbinhsumvay.jpg',CAST(3000000 AS Decimal(18, 0)), CAST(3200000 AS Decimal(18, 0)), 50, 
'Giỏ tre lục bình mang đến vẻ đẹp mộc mạc và gần gũi, được làm từ tre tự nhiên và đan hoàn toàn bằng tay. Sản phẩm thể hiện rõ nét sự giản dị nhưng không kém phần tinh tế, phù hợp để đựng các món quà hoặc trang trí không gian sống. Mỗi chiếc giỏ là kết quả của quá trình lao động cần mẫn của người thợ, tạo nên một sản phẩm đầy ý nghĩa, tượng trưng cho sự đoàn kết và sum vầy.', 
'L0012', NULL, 2),
('SP050', 'Giỏ cói Phúc Lộc Tết', 'giocoiphucloctet.jpg',CAST(2000000 AS Decimal(18, 0)), CAST(2500000 AS Decimal(18, 0)), 50, 
'Giỏ cói Phúc Lộc Tết được làm từ cói tự nhiên, mang ý nghĩa phong thủy tốt lành, là biểu tượng của phúc lộc và may mắn. Sản phẩm với thiết kế tinh tế, màu sắc bắt mắt và được gia công tỉ mỉ, phù hợp để làm quà tặng trong các dịp lễ Tết, giúp mang lại may mắn và thịnh vượng cho người nhận. Giỏ cói không chỉ là một món quà ý nghĩa mà còn là vật trang trí tuyệt đẹp cho không gian nhà cửa, mang đến cảm giác ấm cúng và an lành.', 
'L0012', NULL, 4),
('SP051', 'Giỏ mây quà tặng sum vầy', 'giomayquatangsumvay.jpg',CAST(2500000 AS Decimal(18, 0)), CAST(2700000 AS Decimal(18, 0)), 50, 
'Giỏ mây quà tặng được làm từ nguyên liệu mây tự nhiên, mang đến sự gần gũi và ấm áp. Sản phẩm có thiết kế đẹp mắt với không gian chứa rộng rãi, phù hợp cho các dịp sum vầy gia đình hoặc làm quà tặng cho bạn bè. Mỗi chiếc giỏ là sự kết hợp giữa chất liệu mây bền chắc và nghệ thuật đan lát thủ công tinh xảo, giúp tạo nên một sản phẩm vừa thẩm mỹ vừa tiện dụng, là biểu tượng cho sự đoàn kết và yêu thương.', 
'L0012', NULL, 7),
('SP052', 'Giỏ tre quà Tết may mắn', 'giotrequatetmayman.jpg',CAST(4000000 AS Decimal(18, 0)), CAST(4400000 AS Decimal(18, 0)), 50,
'Giỏ tre quà Tết được làm thủ công từ tre, với các họa tiết và màu sắc tượng trưng cho sự may mắn và thịnh vượng. Sản phẩm được gia công tỉ mỉ, mỗi chi tiết đều được chăm chút kỹ lưỡng, mang lại sự khác biệt và độc đáo. Giỏ tre này là lựa chọn lý tưởng cho các dịp Tết, giúp mang lại phúc lộc và may mắn cho gia đình và người nhận, đồng thời thể hiện sự quan tâm và trân trọng của người tặng.', 
'L0012', NULL, 1),

-- ĐÈN TRANG TRÍ
-- 10 
('SP053', 'Đèn lồng guốc để bàn', 'denlongguocdeban.jpg',CAST(1000000 AS Decimal(18, 0)), CAST(1200000 AS Decimal(18, 0)), 15, 
'Đèn lồng guốc để bàn với thiết kế độc đáo và tinh tế, mang lại ánh sáng ấm áp và tạo điểm nhấn cho không gian sống của bạn. Sản phẩm được làm từ tre tự nhiên, thân thiện với môi trường và phù hợp cho việc trang trí bàn làm việc hoặc bàn ăn.',
'L0013', NULL, 12),
('SP054', 'Đèn trần tre hình lợp chồng', 'dentrantrehinhlopchong.jpg',CAST(2000000 AS Decimal(18, 0)), CAST(2500000 AS Decimal(18, 0)), 10, 
'Đèn trần tre hình lợp chồng với thiết kế hiện đại, mang đến ánh sáng nhẹ nhàng và không gian ấm cúng. Sản phẩm được làm thủ công từ tre tự nhiên, rất phù hợp cho các không gian phòng khách hoặc phòng ngủ.',
'L0013', NULL, 12),
('SP055', 'Bộ đèn thả hình mây tre hình oval', 'bodenthahinhmaytrehinhoval.jpg',CAST(2700000 AS Decimal(18, 0)), CAST(3000000 AS Decimal(18, 0)), 12, 
'Bộ đèn thả hình mây tre hình oval với thiết kế tinh tế và hiện đại, mang lại ánh sáng dịu nhẹ và tạo không gian ấm áp cho ngôi nhà của bạn. Sản phẩm được làm từ tre tự nhiên, bền đẹp và thân thiện với môi trường.',
'L0013', NULL, 1),
('SP056', 'Đèn bàn hình oval đan xếp lưới', 'denbanhinhovaldanxepluoi.jpg',CAST(1600000 AS Decimal(18, 0)), CAST(1800000 AS Decimal(18, 0)), 20, 
'Đèn bàn hình oval đan xếp lưới mang đến vẻ đẹp nghệ thuật và phong cách độc đáo cho không gian của bạn. Sản phẩm được làm từ tre tự nhiên và đan tay, tạo nên sự khác biệt và thu hút.',
'L0013', NULL, 8),
('SP057', 'Đèn thả hình vòm tre', 'denthahinhvomtre.jpg',CAST(1900000 AS Decimal(18, 0)), CAST(2100000 AS Decimal(18, 0)), 18, 
'Đèn thả hình vòm tre với thiết kế ấn tượng và độc đáo, mang lại ánh sáng ấm áp và tạo điểm nhấn cho không gian sống. Sản phẩm được làm từ tre tự nhiên, thân thiện với môi trường và phù hợp cho các không gian hiện đại.',
'L0013', NULL, 9),
('SP058', 'Đèn thả hình trụ từ lục bình', 'denthahinhtrutulucbinh.jpg',CAST(2000000 AS Decimal(18, 0)), CAST(2300000 AS Decimal(18, 0)), 16, 
'Đèn thả hình trụ từ lục bình mang lại vẻ đẹp tự nhiên và ấm cúng, phù hợp cho các không gian phòng khách hoặc phòng ngủ. Sản phẩm được làm thủ công từ lục bình tự nhiên, thân thiện với môi trường.',
'L0013', NULL, 12),
('SP059', 'Đèn thả hình tròn bằng mây tre', 'denthahinhtronbangmaytre.jpg',CAST(1700000 AS Decimal(18, 0)), CAST(1900000 AS Decimal(18, 0)), 22, 
'Đèn thả hình tròn bằng mây tre mang lại ánh sáng dịu nhẹ và tạo không gian ấm áp. Sản phẩm được làm từ tre tự nhiên, phù hợp cho việc trang trí phòng khách hoặc phòng ăn.',
'L0013', NULL, 12),
('SP060', 'Đèn thả hình giọt nước bằng cói', 'denthahinhgiotnuocbangcoi.jpg',CAST(1800000 AS Decimal(18, 0)), CAST(2000000 AS Decimal(18, 0)), 25, 
'Đèn thả hình giọt nước bằng cói với thiết kế độc đáo và tinh tế, mang lại ánh sáng ấm áp và không gian sống thư giãn. Sản phẩm được làm từ cói tự nhiên, thân thiện với môi trường.',
'L0013', NULL, 12),
('SP061', 'Đèn trần hình cầu bằng cói xoắn', 'dentranhinhcaubangcoixoan.jpg',CAST(2500000 AS Decimal(18, 0)), CAST(2700000 AS Decimal(18, 0)), 14, 
'Đèn trần hình cầu bằng cói xoắn với thiết kế ấn tượng, tạo điểm nhấn cho không gian nội thất. Sản phẩm mang lại ánh sáng nhẹ nhàng và được làm từ cói tự nhiên, thân thiện với môi trường.',
'L0013', NULL, 8),
('SP062', 'Đèn treo bằng mây tre họa tiết lưới', 'dentrantrehinhlopchong.jpg',CAST(1500000 AS Decimal(18, 0)), CAST(1700000 AS Decimal(18, 0)), 18,
'Đèn treo bằng mây tre họa tiết lưới được làm từ mây tre tự nhiên, mang lại cảm giác ấm cúng và gần gũi cho không gian sống. Thiết kế họa tiết lưới tinh tế giúp ánh sáng lan tỏa nhẹ nhàng, tạo không khí thư giãn. Phù hợp trang trí phòng khách, phòng ăn hay quán cà phê với phong cách mộc mạc, tự nhiên.',
'L0013', NULL, 10),

-- LỒNG THÚ CƯNG
-- 5
('SP063', 'Lồng chim gỗ mun văn', 'longchimgomunvan.jpg',CAST(900000 AS Decimal(18, 0)), CAST(1000000 AS Decimal(18, 0)), 50, 
'Lồng chim làm từ gỗ mun cao cấp, với hoa văn tinh xảo và thiết kế độc đáo. Lồng thích hợp để nuôi chim cảnh, mang lại vẻ đẹp sang trọng và cổ điển. Được chế tác từ gỗ mun tự nhiên, sản phẩm không chỉ bền bỉ mà còn mang một giá trị thẩm mỹ cao. Hoa văn trên lồng được chạm khắc tỉ mỉ, thể hiện sự khéo léo của người nghệ nhân. Lồng chim này phù hợp cho những người yêu thích phong cách cổ điển và muốn mang đến cho không gian sống một nét sang trọng và khác biệt.', 
'L0014', NULL, 12),
('SP064', 'Lồng khuyên đục', 'longkhuyenduc.jpg',CAST(2900000 AS Decimal(18, 0)), CAST(3500000 AS Decimal(18, 0)), 50, 
'Lồng chim khuyên được đục thủ công với các họa tiết sinh động, tạo nên sự tinh tế và nổi bật. Lồng phù hợp cho những ai yêu thích sự tỉ mỉ và nghệ thuật. Với chất liệu gỗ bền đẹp và thiết kế hoa văn tinh xảo, sản phẩm mang lại vẻ đẹp đầy nghệ thuật và thu hút. Lồng chim khuyên này không chỉ là nơi nuôi dưỡng những chú chim yêu quý mà còn là một tác phẩm nghệ thuật thể hiện sự khéo léo và công phu của người thợ làm lồng.', 
'L0014', NULL, 12),
('SP065', 'Lồng chim handmade bằng tre', 'longchimhandmadebangtre.jpg',CAST(1900000 AS Decimal(18, 0)), CAST(2100000 AS Decimal(18, 0)), 50, 
'Lồng chim được làm thủ công từ tre tự nhiên, mang phong cách mộc mạc và gần gũi với thiên nhiên. Sản phẩm bền đẹp, thích hợp cho việc nuôi chim cảnh. Với thiết kế đơn giản nhưng không kém phần tinh tế, lồng chim này đem lại cảm giác gần gũi, ấm cúng. Chất liệu tre tự nhiên giúp cho lồng chim luôn thoáng mát và thân thiện với môi trường. Đây là lựa chọn lý tưởng cho những ai yêu thích sự giản dị và tự nhiên trong không gian sống.',
'L0014', NULL, 8),
('SP066', 'Lồng chim Mata Puteh', 'longchimmataputeh.jpg',CAST(1300000 AS Decimal(18, 0)), CAST(1500000 AS Decimal(18, 0)), 50, 
'Lồng chim Mata Puteh được thiết kế để nuôi giống chim nhỏ, với kiểu dáng đẹp và chắc chắn. Sản phẩm mang lại sự tiện nghi và thoải mái cho chim. Lồng có cấu trúc vững chắc, được làm từ vật liệu cao cấp, giúp bảo vệ chim khỏi các tác động bên ngoài. Thiết kế thông thoáng giúp chim luôn cảm thấy thoải mái và tự do bay nhảy. Đây là lựa chọn hoàn hảo cho những người yêu thích chim nhỏ và muốn tạo cho chúng một môi trường sống tốt nhất.', 
'L0014', NULL, 8),
('SP067', 'Lồng chim đục bằng tre', 'longchimducbangtre.jpg',CAST(4400000 AS Decimal(18, 0)), CAST(4800000 AS Decimal(18, 0)), 50, 
'Lồng chim được đục từ tre với hoa văn tinh xảo, tạo nên vẻ đẹp độc đáo và nghệ thuật. Sản phẩm phù hợp cho việc trang trí và nuôi chim cảnh. Các hoa văn được chạm trổ thủ công với độ chính xác cao, mang lại giá trị thẩm mỹ đặc biệt. Lồng chim này không chỉ là nơi ở cho những chú chim mà còn là một điểm nhấn trong không gian trang trí, thể hiện sự tinh tế và phong cách cá nhân của chủ nhân.', 
'L0014', NULL, 8),

-- TÚI XÁCH
-- 8
('SP068', 'Túi đan tay truyền thống', 'tuidantaytruyenthong.jpg',CAST(100000 AS Decimal(18, 0)), CAST(134000 AS Decimal(18, 0)), 50, 
'Túi đan tay thủ công, mang phong cách truyền thống với những đường nét tinh tế. Sản phẩm phù hợp với nhiều loại trang phục, từ giản dị đến trang trọng, giúp tôn lên vẻ đẹp tự nhiên và đậm chất truyền thống của người sử dụng. Túi được làm từ chất liệu tự nhiên, bền đẹp và thân thiện với môi trường.', 
'L0015', NULL, 12),
('SP069', 'Túi tròn đeo chéo thời trang', 'tuitrondeocheothoitrang.jpg',CAST(500000 AS Decimal(18, 0)), CAST(564000 AS Decimal(18, 0)), 50, 
'Túi tròn đeo chéo với thiết kế thời trang, mang đến sự tiện lợi và phong cách hiện đại. Túi phù hợp cho các buổi dạo phố, đi chơi hay gặp gỡ bạn bè. Được làm từ chất liệu cao cấp, túi không chỉ đẹp mắt mà còn bền chắc, giúp người dùng luôn tự tin trong mọi hoạt động.', 
'L0015', NULL, 10),
('SP070', 'Túi mây truyền thống', 'tuimaytruyenthong.jpg',CAST(100000 AS Decimal(18, 0)), CAST(120000 AS Decimal(18, 0)), 50, 
'Túi làm từ mây tự nhiên, mang đến vẻ đẹp mộc mạc, gần gũi với thiên nhiên. Với thiết kế truyền thống, túi phù hợp cho những ai yêu thích phong cách giản dị, thuần khiết. Sản phẩm có thể kết hợp với nhiều loại trang phục khác nhau, từ váy maxi nhẹ nhàng đến quần áo thường ngày, tạo nên điểm nhấn đầy tinh tế.', 
'L0015', NULL, 8),
('SP071', 'Túi nữ dáng hộp sành điệu', 'tuinudangtuihopsanhdieu.jpg',CAST(200000 AS Decimal(18, 0)), CAST(240000 AS Decimal(18, 0)), 50, 
'Túi nữ dáng hộp với thiết kế sành điệu, phù hợp với phong cách hiện đại. Túi có kiểu dáng cứng cáp, giúp giữ được hình dạng và bảo vệ tốt các vật dụng bên trong. Với thiết kế thời trang, túi này rất phù hợp cho những buổi gặp gỡ, sự kiện hoặc đi làm, mang lại vẻ đẹp thanh lịch và chuyên nghiệp.', 
'L0015', NULL, 10),
('SP072', 'Túi tre nữ thắt nơ xinh xắn', 'tuitrenuthatno.jpg',CAST(20000 AS Decimal(18, 0)), CAST(34000 AS Decimal(18, 0)), 50, 
'Túi tre với thiết kế thắt nơ xinh xắn, mang đến vẻ đẹp dịu dàng và nữ tính. Túi được làm từ chất liệu tre tự nhiên, vừa thân thiện với môi trường vừa mang lại cảm giác nhẹ nhàng, thoải mái khi sử dụng. Đây là phụ kiện hoàn hảo cho các buổi dạo chơi hoặc hẹn hò, giúp tôn lên phong cách đáng yêu và duyên dáng của người dùng.',
'L0015', NULL, 12),
('SP073', 'Túi nữ lục bình', 'tuinulucbinh.jpg',CAST(40000 AS Decimal(18, 0)), CAST(60000 AS Decimal(18, 0)), 50, 
'Túi nữ được làm từ lục bình, mang lại sự độc đáo và thân thiện với môi trường. Thiết kế đơn giản nhưng không kém phần tinh tế, phù hợp cho những ai yêu thích phong cách tự nhiên, mộc mạc. Túi có thể sử dụng trong nhiều dịp khác nhau như đi dạo, mua sắm hay đi picnic, tạo cảm giác gần gũi với thiên nhiên.', 
'L0015', NULL, 8),
('SP074', 'Túi cói sang trọng', 'tuicoisangtrong.jpg',CAST(250000 AS Decimal(18, 0)), CAST(300000 AS Decimal(18, 0)), 50, 
'Túi cói với thiết kế sang trọng, phù hợp cho các sự kiện và buổi tiệc. Túi mang đến vẻ đẹp thanh lịch, đẳng cấp, là sự lựa chọn hoàn hảo cho những dịp đặc biệt. Chất liệu cói tự nhiên kết hợp với kiểu dáng hiện đại giúp túi trở thành điểm nhấn nổi bật, tôn lên sự sang trọng và quý phái của người sử dụng.', 
'L0015', NULL, 10),
('SP075', 'Túi thời trang đan họa tiết thưa', 'tuithoitrangdanthua.jpg',CAST(400000 AS Decimal(18, 0)), CAST(455000 AS Decimal(18, 0)), 50, 
'Túi thời trang với họa tiết đan thưa, tạo điểm nhấn độc đáo và phong cách riêng biệt. Túi phù hợp cho những ai yêu thích sự phá cách và mới mẻ, có thể sử dụng trong các buổi dạo phố, gặp gỡ bạn bè hay đi du lịch. Sản phẩm không chỉ mang đến vẻ đẹp thẩm mỹ mà còn rất tiện dụng với không gian chứa rộng rãi.', 
'L0015', NULL, 12);
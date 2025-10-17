document.addEventListener('DOMContentLoaded', function () {
    const pearlData = {
        "Đen": {
            title: "NGỌC TRAI MÀU ĐEN",
            description: `Ngọc trai biển màu đen hay còn được gọi là ngọc trai Tahiti là món trang sức được
                         nhiều người ưa chuộng. Ngọc trai đen không chỉ có ánh màu quyến rũ huyền bí mà nó
                         còn mang lại cảm giác lôi cuốn, muốn khám phá cho người đối diện. Màu đen vốn là màu
                         sắc mạnh, khó lấn át nên theo suy nghĩ nhiều người thì những người thích sử dụng màu
                         đen thường là những người mạnh mẽ, độc lập, khó bị đánh bại.`,
            imageSrc: "images/black-pearls.jpg" 
        },
        "Trắng": {
            title: "NGỌC TRAI MÀU TRẮNG",
            description: `Màu trắng tượng trưng cho tâm hồn ngây thơ, vẻ đẹp thánh thiện. Ngọc trai màu
                         trắng được nhiều chị em phụ nữ yêu thích là do nó vừa mang sự nhẹ nhàng, thanh nhã, nữ
                         tính vừa mang nét mạnh mẽ, kiêu kỳ. Màu trắng thuần khiết cùng với ánh ngũ sắc phản
                         chiếu từ bề mặt ngọc trai đem đến cho chủ nhân vẻ đẹp thanh tân, diễm lệ. Trang sức làm
                         từ ngọc trai trắng mang đến cho người đeo vẻ đẹp thân thiện, dịu dàng, dễ mến, từ đó dễ
                         dàng lấy được thiện cảm từ những người xung quanh. Ngoài tác dụng làm đẹp ngọc trai
                         trắng còn giúp người đeo có tinh thần sảng khoái, suy nghĩ tích cực và thanh thản.`,
            imageSrc: "images/white-pearls.jpg"
        },
        "Vàng": {
            title: "NGỌC TRAI MÀU VÀNG",
            description: `Tại các nước phương đông, màu vàng tượng trưng cho sự giàu sang, sung túc còn
                         đối với các quốc gia phương tây thì màu vàng biểu thị cho sự may mắn và hạnh phúc.
                         Bình thường người ta ít sử dụng màu vàng bởi màu sắc có phần sáng chói, rực rỡ nhưng
                         đối với ngọc trai, sắc vàng tạo nên ánh màu dịu nhẹ, tinh tế và quyến rũ. Ngọc trai màu
                         vàng tạo cho người ta có cảm giác về sự giàu sang, phú quý. Màu vàng còn là biểu tượng
                         của năng lượng, sự hiểu biết và lạc quan. Những người phụ nữa mang trang sức ngọc trai
                         màu vàng thể hiện nét đẹp đam mê, tràn đầy nhiệt huyết. Ngọc trai South Sea là dòng
                         ngọc trai có màu vàng kim phổ biến nhất.`,
            imageSrc: "images/golden-pearls.jpg" 
        },
        "Tím": {
            title: "NGỌC TRAI MÀU TÍM",
            description: `Ngọc trai màu tím là biểu tượng của sự thủy chung, son sắt một lòng, bởi vậy nên
                         trang sức ngọc trai màu tím được rất nhiều chị em ưa thích sử dụng trong những sự kiện
                         quan trọng. Ngọc trai màu tím cũng là một trong những loại ngọc trai có màu sắc tự nhiên
                         truyền thống. Nhưng hiện nay với công nghệ mạ tĩnh điện cao cấp, người ta có thể làm
                         nên những viên ngọc trai ánh tím tuyệt đẹp. Màu sắc tím còn đại diện cho sự may mắn
                         bền lâu cùng với sự trường tồn của nhan sắc. Đây chính là một trong những lý do chính
                         khiến ngọc trai màu tím ngày càng được nhiều người lựa chọn.`,
            imageSrc: "images/purple-pearl.jpeg"
        }
    };

    const navLinks = document.querySelectorAll('#menu-bar ul li a');
    const titleElement = document.getElementById('tieude');
    const descriptionElement = document.getElementById('mota');
    const imageElement = document.getElementById('imgPearl');

    function Reset() {
        navLinks.forEach(link => {
            link.style.backgroundColor = '#FBFFDE'; 
            link.style.color = '#6E4B35';     
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            Reset();

            this.style.backgroundColor = '#6E4B35'; 
            this.style.color = '#FBFFDE';   

            const key = this.textContent;

            if (pearlData[key]) {
                const data = pearlData[key];
                titleElement.textContent = data.title;
                descriptionElement.textContent = data.description;
                imageElement.src = data.imageSrc;
                imageElement.alt = data.title;
            }
        });
    });
    
    if(navLinks.length > 0) {
        navLinks[0].click();
    }
});

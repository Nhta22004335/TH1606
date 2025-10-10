<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa Đơn Bán Hàng</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="invoice-box">
        <header>
            <div class="logo">
                <h1>CÔNG TY ABC</h1>
            </div>
            <div class="company-info">
                <strong>Địa chỉ:</strong> 123 Đường XYZ, Quận 1, TP. HCM<br>
                <strong>Điện thoại:</strong> (028) 1234 5678<br>
                <strong>Email:</strong> info@congtyabc.com
            </div>
        </header>

        <section class="invoice-header">
            <h2>HÓA ĐƠN BÁN HÀNG</h2>
            <div class="invoice-meta">
                <p><strong>Mã Hóa Đơn:</strong> INV-2025-001</p>
                <p><strong>Ngày Lập:</strong> 10/10/2025</p>
                <p><strong>Ngày Thanh Toán:</strong> 17/10/2025</p>
            </div>
        </section>

        <section class="billing-info">
            <div class="client-info">
                <h3>Thông Tin Khách Hàng</h3>
                <p><strong>Tên Khách Hàng:</strong> Nguyễn Văn A</p>
                <p><strong>Địa Chỉ:</strong> 456 Đường UVW, Quận 3, TP. HCM</p>
                <p><strong>Điện Thoại:</strong> 0901 123 456</p>
                <p><strong>Email:</strong> nguyen.vana@email.com</p>
            </div>
        </section>

        <section class="item-details">
            <h3>Chi Tiết Sản Phẩm/Dịch Vụ</h3>
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mô Tả</th>
                        <th>Đơn Giá</th>
                        <th>Số Lượng</th>
                        <th>Thành Tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Dịch vụ Thiết kế Website</td>
                        <td>10.000.000 VNĐ</td>
                        <td>1</td>
                        <td>10.000.000 VNĐ</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Phí duy trì Hosting (1 năm)</td>
                        <td>1.200.000 VNĐ</td>
                        <td>1</td>
                        <td>1.200.000 VNĐ</td>
                    </tr>
                    </tbody>
            </table>
        </section>

        <section class="summary">
            <div class="totals">
                <table>
                    <tr>
                        <td><strong>Tạm Tính:</strong></td>
                        <td>11.200.000 VNĐ</td>
                    </tr>
                    <tr>
                        <td><strong>Thuế (10%):</strong></td>
                        <td>1.120.000 VNĐ</td>
                    </tr>
                    <tr>
                        <td><strong>Tổng Cộng:</strong></td>
                        <td><span class="total-amount">12.320.000 VNĐ</span></td>
                    </tr>
                </table>
            </div>
        </section>

        <footer>
            <div class="notes">
                <p><strong>Ghi chú:</strong> Vui lòng thanh toán trước ngày 17/10/2025. Cảm ơn quý khách!</p>
            </div>
            <div class="signature">
                <p>Người Lập Hóa Đơn</p>
                <br><br>
                <p>_________________________</p>
                <p>(Ký và ghi rõ họ tên)</p>
            </div>
        </footer>

        <button onclick="window.print()" class="print-button">In Hóa Đơn</button>

    </div>

</body>
</html>
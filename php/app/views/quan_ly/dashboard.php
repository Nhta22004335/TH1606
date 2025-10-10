<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hero Carousel Bất Động Sản</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLMDJ8S+anWHD9+lWlI/Bw4g8q6uL+yqT2S8cRAB6XQp9r/9C7M/dFm3J8mN/K2uYmQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Tùy chỉnh nhỏ để đảm bảo font Inter được sử dụng */
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body>

<!-- ✅ Hero Carousel Chính -->
<section id="heroCarousel" class="relative h-[52vh] md:h-[62vh] lg:h-[72vh] min-h-[400px] overflow-hidden shadow-2xl rounded-lg">

    <!-- Slide 1: Tìm nhà mơ ước -->
    <div class="slide absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-100"
        style="background-image: linear-gradient(rgba(10,30,60,0.65), rgba(10,30,60,0.4)), 
        url('https://images.unsplash.com/photo-1501183638710-841dd1904471?auto=format&fit=crop&w=1920&q=80');">
        
        <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-6">
            <h2 class="text-3xl md:text-3xl font-extrabold mb-4 drop-shadow-2xl animate-in fade-in slide-in-from-top-4 duration-700">
                Tìm ngôi nhà mơ ước của bạn
            </h2>
            <p class="mb-8 text-xl md:text-xl font-light opacity-90 max-w-2xl drop-shadow-lg animate-in fade-in duration-1000 delay-200">
                Khám phá hàng ngàn bất động sản uy tín, chất lượng cao trên toàn quốc.
            </p>
            
        </div>
    </div>

    <!-- Slide 2: Không gian sống xanh -->
    <div class="slide absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-0"
        style="background-image: linear-gradient(rgba(10,30,60,0.65), rgba(10,30,60,0.4)), 
        url('https://images.unsplash.com/photo-1570129477492-45c003edd2be?auto=format&fit=crop&w=1920&q=80');">
        
        <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-6">
            <h2 class="text-3xl md:text-3xl font-extrabold mb-4 drop-shadow-2xl">
                Không gian sống xanh & hiện đại
            </h2>
            <p class="mb-8 text-xl md:text-xl font-light opacity-90 max-w-2xl drop-shadow-lg">
                Trải nghiệm cuộc sống tiện nghi trong các khu đô thị sinh thái.
            </p>
           
        </div>
    </div>

    <!-- Slide 3: Đầu tư thông minh -->
    <div class="slide absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-0"
        style="background-image: linear-gradient(rgba(10,30,60,0.65), rgba(10,30,60,0.4)), 
        url('https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=1920&q=80');">
        
        <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-6">
            <h2 class="text-3xl md:text-3xl font-extrabold mb-4 drop-shadow-2xl">
                Đầu tư thông minh, sinh lời bền vững
            </h2>
            <p class="mb-8 text-xl md:text-xl font-light opacity-90 max-w-2xl drop-shadow-lg">
                Chọn lựa bất động sản tiềm năng để gia tăng giá trị tài sản trong tương lai.
            </p>
            
        </div>
    </div>

    <!-- 🔘 Dots điều hướng -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 flex space-x-3 z-10">
        <button class="dot w-3.5 h-3.5 rounded-full bg-white transition-all duration-300 hover:scale-125 shadow-md active-dot"></button>
        <button class="dot w-3.5 h-3.5 rounded-full bg-white opacity-40 transition-all duration-300 hover:opacity-70"></button>
        <button class="dot w-3.5 h-3.5 rounded-full bg-white opacity-40 transition-all duration-300 hover:opacity-70"></button>
    </div>
</section>

<script>
    const carousel = document.getElementById("heroCarousel");
    const slides = carousel.querySelectorAll(".slide");
    const dots = carousel.querySelectorAll(".dot");
    let current = 0;
    let autoSlideInterval;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.style.opacity = i === index ? "1" : "0";
            
            // Cập nhật trạng thái dot
            dots[i].classList.remove("active-dot", "opacity-70");
            dots[i].classList.add("opacity-40");

            if (i === index) {
                dots[i].classList.add("active-dot");
                dots[i].classList.remove("opacity-40");
            }
        });
        current = index;
    }

    function nextSlide() {
        current = (current + 1) % slides.length;
        showSlide(current);
    }
    
    // Khởi tạo tự động chuyển slide
    function startAutoSlide() {
        if (autoSlideInterval) clearInterval(autoSlideInterval);
        autoSlideInterval = setInterval(nextSlide, 6000); // Tăng thời gian chờ lên 6 giây
    }
    
    // Dừng và khởi động lại interval khi tương tác
    function resetAutoSlide() {
        clearInterval(autoSlideInterval);
        startAutoSlide();
    }

    // Cho phép click chọn slide
    dots.forEach((dot, index) => {
        dot.addEventListener("click", () => {
            showSlide(index);
            resetAutoSlide(); // Khởi động lại timer sau khi người dùng click
        });
    });

    // Thêm hiệu ứng cho dot đang active
    const style = document.createElement('style');
    style.innerHTML = `
        .active-dot {
            opacity: 1 !important;
            transform: scale(1.2);
            border: 2px solid rgba(255, 255, 255, 0.5);
            width: 1rem;
            height: 1rem;
        }
    `;
    document.head.appendChild(style);

    // Khởi chạy khi tải trang
    document.addEventListener("DOMContentLoaded", () => {
        showSlide(0); // Đảm bảo slide đầu tiên được hiển thị đúng
        startAutoSlide(); 
    });
</script>

</body>
</html>

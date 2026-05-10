document.addEventListener("DOMContentLoaded", function() {
    // 1. Filter Kategori User
    const filterBtns = document.querySelectorAll(".filter-btn");
    const productCards = document.querySelectorAll(".product-card");
    const searchInput = document.getElementById("searchProduct");

    if(filterBtns.length > 0) {
        filterBtns.forEach(btn => {
            btn.addEventListener("click", function() {
                filterBtns.forEach(b => b.classList.remove("active"));
                this.classList.add("active");
                const filter = this.getAttribute("data-filter");
                productCards.forEach(card => {
                    if (filter === "Semua" || card.getAttribute("data-kategori") === filter) {
                        card.style.display = "flex";
                    } else {
                        card.style.display = "none";
                    }
                });
            });
        });
    }

    if(searchInput) {
        searchInput.addEventListener("keyup", function() {
            const searchTerm = this.value.toLowerCase();
            productCards.forEach(card => {
                const productName = card.querySelector(".product-name").textContent.toLowerCase();
                if (productName.includes(searchTerm)) { card.style.display = "flex"; } 
                else { card.style.display = "none"; }
            });
        });
    }

    // 2. Dynamic Form Register
    const daftarSebagai = document.getElementById("daftar_sebagai");
    if(daftarSebagai) {
        daftarSebagai.addEventListener("change", function() {
            document.getElementById("field-nis").style.display = "none";
            document.getElementById("field-nip").style.display = "none";
            document.getElementById("field-bagian").style.display = "none";
            if(this.value === "Murid") document.getElementById("field-nis").style.display = "block";
            else if(this.value === "Guru") document.getElementById("field-nip").style.display = "block";
            else if(this.value === "Internal Sekolah") document.getElementById("field-bagian").style.display = "block";
        });
    }

    // 3. Modal Logic
    window.openModal = function(modalId) {
        document.getElementById(modalId).style.display = 'flex';
    }
    window.closeModal = function(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // 4. Profile Dropdown Toggle (Click)
    const profileToggle = document.getElementById('profileToggle');
    const profileDropdown = document.getElementById('profileDropdown');

    if(profileToggle && profileDropdown) {
        profileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            if (!profileDropdown.contains(e.target) && !profileToggle.contains(e.target)) {
                profileDropdown.classList.remove('show');
            }
        });
    }

    // 5. Hamburger Menu Toggle
    const hamburger = document.getElementById('hamburgerBtn');
    const navMenu = document.getElementById('navMenu');

    if(hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('show');
        });
    }
});
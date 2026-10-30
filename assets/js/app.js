(function () {
    "use strict";

    /* ---- Konfirmasi: <form data-konfirmasi="..."> atau <button data-konfirmasi="..."> ---- */
    document.addEventListener("submit", function (e) {
        var pesan = e.target.getAttribute("data-konfirmasi") ||
            (e.submitter && e.submitter.getAttribute("data-konfirmasi"));
        if (pesan && !window.confirm(pesan)) {
            e.preventDefault();
        }
    }, true);

    /* ---- Toast kecil ---- */
    var toastTimer;
    function toast(teks, error) {
        var el = document.querySelector(".toast");
        if (!el) {
            el = document.createElement("div");
            el.className = "toast";
            el.setAttribute("role", "status");
            document.body.appendChild(el);
        }
        el.textContent = teks;
        el.classList.toggle("error", !!error);
        el.style.display = "block";
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { el.style.display = "none"; }, 2200);
    }

    /* ---- Tambah ke keranjang tanpa reload (jatuh ke submit biasa jika JS mati/gagal) ---- */
    document.addEventListener("submit", function (e) {
        var form = e.target;
        if (e.defaultPrevented || !form.classList.contains("form-keranjang-ajax")) return;
        if (!form.hasAttribute("data-ajax")) return; // hanya form di halaman menu yang ber-atribut ini
        e.preventDefault();

        var data = new FormData(form);
        fetch(form.action, {
            method: "POST",
            body: data,
            headers: { "X-Requested-With": "fetch", "Accept": "application/json" },
            credentials: "same-origin"
        })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (typeof j.jumlah === "number") {
                    document.querySelectorAll("[data-cart-count]").forEach(function (n) { n.textContent = j.jumlah; });
                }
                if (j.ok && j.muat_ulang) { window.location.reload(); return; }
                toast(j.pesan || (j.ok ? "Ditambahkan" : "Gagal"), !j.ok);
            })
            .catch(function () { form.removeAttribute("data-ajax"); form.submit(); });
    });

    /* ---- Pencarian & filter: <input data-filter-input>, <select data-filter-select="atribut">,
            item: [data-filter-item] (atribut data-<atribut> dicocokkan dengan nilai select) ---- */
    var cari = document.querySelector("[data-filter-input]");
    var selects = document.querySelectorAll("[data-filter-select]");
    var moreBtn = document.querySelector("[data-more]");

    function terapkanFilter() {
        var q = cari ? cari.value.trim().toLowerCase() : "";
        var aktif = q !== "";
        selects.forEach(function (s) { if (s.value !== "") aktif = true; });
        var dibuka = moreBtn && moreBtn.getAttribute("data-state") === "open";
        var tampil = 0;

        document.querySelectorAll("[data-filter-item]").forEach(function (item) {
            var cocok = true;
            if (q !== "" && item.textContent.toLowerCase().indexOf(q) === -1) cocok = false;
            selects.forEach(function (s) {
                var attr = s.getAttribute("data-filter-select");
                if (s.value !== "" && item.getAttribute("data-" + attr) !== s.value) cocok = false;
            });
            if (!aktif) { item.hidden = item.hasAttribute("data-extra") && !dibuka; }
            else { item.hidden = !cocok; }
            if (!item.hidden) tampil++;
        });

        var kosong = document.querySelector("[data-filter-empty]");
        if (kosong) kosong.hidden = tampil > 0;
        if (moreBtn) moreBtn.parentNode.hidden = aktif; // saat mencari, tombol "lebih banyak" disembunyikan
    }
    if (cari) cari.addEventListener("input", terapkanFilter);
    selects.forEach(function (s) { s.addEventListener("change", terapkanFilter); });

    /* ---- Tampilkan lebih banyak lapak: <button data-more data-state="closed"> ---- */
    if (moreBtn) {
        moreBtn.addEventListener("click", function () {
            var buka = moreBtn.getAttribute("data-state") !== "open";
            moreBtn.setAttribute("data-state", buka ? "open" : "closed");
            moreBtn.textContent = buka ? "Tutup" : "Tampilkan Lebih Banyak";
            terapkanFilter();
        });
    }

    /* ---- Stepper stok: <div class="stepper"><button data-step="-1">…<input type=number>…<button data-step="1"> ---- */
    document.querySelectorAll(".stepper").forEach(function (box) {
        var input = box.querySelector("input[type=number]");
        box.querySelectorAll("[data-step]").forEach(function (b) {
            b.addEventListener("click", function () {
                var v = parseInt(input.value, 10) || 0;
                v += parseInt(b.getAttribute("data-step"), 10);
                input.value = Math.max(0, Math.min(100000, v));
            });
        });
    });

    /* ---- Tampilkan / sembunyikan password: <button data-toggle-password="#id"> ---- */
    document.querySelectorAll("[data-toggle-password]").forEach(function (btn) {
        var input = document.querySelector(btn.getAttribute("data-toggle-password"));
        var ikon = btn.querySelector(".ic");
        if (!input) return;
        btn.addEventListener("click", function () {
            var sembunyi = input.type === "password";
            input.type = sembunyi ? "text" : "password";
            if (ikon) {
                ikon.classList.toggle("ic-eye", !sembunyi);
                ikon.classList.toggle("ic-eye-off", sembunyi);
            }
            btn.setAttribute("aria-label", sembunyi ? "Sembunyikan password" : "Tampilkan password");
        });
    });

    /* ---- Konfirmasi password harus sama: <input data-sama-dengan="#password"> ---- */
    document.querySelectorAll("[data-sama-dengan]").forEach(function (input) {
        var asal = document.querySelector(input.getAttribute("data-sama-dengan"));
        if (!asal) return;
        function cek() {
            input.setCustomValidity(input.value !== asal.value ? "Password tidak sama." : "");
        }
        input.addEventListener("input", cek);
        asal.addEventListener("input", cek);
    });

    /* ---- Pratinjau gambar: <input type=file data-preview="#idImg"> ---- */
    document.querySelectorAll("input[type=file][data-preview]").forEach(function (input) {
        var img = document.querySelector(input.getAttribute("data-preview"));
        if (!img) return;
        input.addEventListener("change", function () {
            var f = input.files && input.files[0];
            if (!f) { img.hidden = true; return; }
            if (f.size > 2097152) { alert("Ukuran gambar maks. 2 MB."); input.value = ""; img.hidden = true; return; }
            img.src = URL.createObjectURL(f);
            img.hidden = false;
        });
    });

    /* ---- Hitung mundur pembayaran: <span data-sisa="detik"> (tampil mm:dd) ---- */
    var hm = document.querySelector("[data-sisa]");
    if (hm) {
        var selesaiPada = Date.now() + parseInt(hm.getAttribute("data-sisa"), 10) * 1000;
        var tick = function () {
            var sisa = Math.max(0, Math.round((selesaiPada - Date.now()) / 1000));
            var m = String(Math.floor(sisa / 60)).padStart(2, "0");
            var d = String(sisa % 60).padStart(2, "0");
            hm.textContent = m + ":" + d;
            if (sisa <= 0) {
                if (hm.parentNode) hm.parentNode.classList.add("expired");
                window.location.reload();
                return;
            }
            setTimeout(tick, 500);
        };
        tick();
    }

    /* ---- Muat ulang otomatis: elemen [data-refresh="detik"] ---- */
    var rf = document.querySelector("[data-refresh]");
    if (rf) {
        setTimeout(function () { window.location.reload(); }, parseInt(rf.getAttribute("data-refresh"), 10) * 1000);
    }
})();

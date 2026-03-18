document.addEventListener("DOMContentLoaded", () => {
    const el = document.querySelector(".about-slide");
    if (!el) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
             if (entry.isIntersecting) {
                el.classList.remove("translate-x-2/5", "translate-x-70", "opacity-0");
                el.classList.add("opacity-100");
                observer.disconnect();
            }
        });
    }
    , { threshold: 0.3 }
    );

    observer.observe(document.querySelector("#about-us"));
});
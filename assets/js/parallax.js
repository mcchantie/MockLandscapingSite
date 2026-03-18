const parallaxItems = document.querySelectorAll("[data-parallax]");

console.log("Parallax items found:", parallaxItems.length);

window.addEventListener("scroll", () => {
    const scrollY = window.scrollY;

    parallaxItems.forEach((el, index) => {
        const speed = 0.15; // lower = subtler
        const translateY = scrollY * speed;
        el.style.transform = `translateY(${translateY}px)`;

        // Debug log (remove after testing)
        if (index === 0) {
            console.log("Scroll Y:", scrollY, "Transform Y:", translateY);
        }
    });
});

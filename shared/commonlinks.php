<!-- for jquery v3.7.1 cdn-->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" />

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

<!-- animate css cdn links (yo maile home page ko jersey page ma main text ma used grya xu) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<script>
document.addEventListener("DOMContentLoaded", function () {
    const elements = document.querySelectorAll(".animate__animated");

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("animate__fadeInDown");
            } else {
                entry.target.classList.remove("animate__fadeInDown");
            }
        });
    }, { threshold: 0.3 });

    elements.forEach(el => observer.observe(el));
});
</script>



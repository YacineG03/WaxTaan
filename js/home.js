// JavaScript pour la page d'accueil
document.addEventListener("DOMContentLoaded", () => {
  // Animation des messages dans le mockup
  const animateMessages = () => {
    const messages = document.querySelectorAll(".message")
    messages.forEach((message, index) => {
      setTimeout(() => {
        message.style.opacity = "0"
        message.style.transform = "translateY(10px)"

        setTimeout(() => {
          message.style.opacity = "1"
          message.style.transform = "translateY(0)"
        }, 200)
      }, index * 800)
    })
  }

  // Répéter l'animation des messages
  setInterval(animateMessages, 4000)

  // Smooth scroll pour les liens
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute("href"))
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })

  // Animation au scroll
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1"
        entry.target.style.transform = "translateY(0)"
      }
    })
  }, observerOptions)

  // Observer les éléments à animer
  document.querySelectorAll(".feature-item").forEach((item) => {
    item.style.opacity = "0"
    item.style.transform = "translateY(20px)"
    item.style.transition = "all 0.6s ease"
    observer.observe(item)
  })

  // Effet parallax léger
  window.addEventListener("scroll", () => {
    const scrolled = window.pageYOffset
    const heroVisual = document.querySelector(".hero-visual")
    if (heroVisual) {
      heroVisual.style.transform = `translateY(${scrolled * 0.1}px)`
    }
  })
})

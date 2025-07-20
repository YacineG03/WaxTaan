// JavaScript pour la page d'accueil
document.addEventListener("DOMContentLoaded", () => {
  // Animation des messages dans le mockup
  const animerMessages = () => {
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
  setInterval(animerMessages, 4000)

  // Smooth scroll pour les liens
  document.querySelectorAll('a[href^="#"]').forEach((ancre) => {
    ancre.addEventListener("click", function (e) {
      e.preventDefault()
      const cible = document.querySelector(this.getAttribute("href"))
      if (cible) {
        cible.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })

  // Animation au scroll
  const optionsObservateur = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observateur = new IntersectionObserver((entrees) => {
    entrees.forEach((entree) => {
      if (entree.isIntersecting) {
        entree.target.style.opacity = "1"
        entree.target.style.transform = "translateY(0)"
      }
    })
  }, optionsObservateur)

  // Observer les éléments à animer
  document.querySelectorAll(".element-fonctionnalite").forEach((element) => {
    element.style.opacity = "0"
    element.style.transform = "translateY(20px)"
    element.style.transition = "all 0.6s ease"
    observateur.observe(element)
  })

  // Effet parallax léger
  window.addEventListener("scroll", () => {
    const defilement = window.pageYOffset
    const visuelHero = document.querySelector(".visuel-hero")
    if (visuelHero) {
      visuelHero.style.transform = `translateY(${defilement * 0.1}px)`
    }
  })
})

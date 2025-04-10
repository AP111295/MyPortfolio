function myFunction() {
    var x = document.getElementById("myLinks");
    if (x.style.display === "block") {
      x.style.display = "none";
    } else {
      x.style.display = "block";
    }
  }

const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      // const slide = entry.target.querySelector('.slide');
      const slide = entry.target;
  
      if (entry.isIntersecting) {
        slide.classList.add('wipe-enter');
      return; // if we added the class, exit the function
      }
  
      // We're not intersecting, so remove the class!
      slide.classList.remove('wipe-enter');
    });
  });
  
  observer.observe(document.querySelector('.tools'));
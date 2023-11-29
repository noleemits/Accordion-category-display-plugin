document.addEventListener('DOMContentLoaded', () => {
    const accordionToggles = document.querySelectorAll('.category-title, .subcategory-title');
  
    accordionToggles.forEach(toggle => {
      toggle.addEventListener('click', () => {
        // Toggle the icon
        const icon = toggle.querySelector('.toggle-icon');
        icon.textContent = icon.textContent === '+' ? '-' : '+';
  
        // Toggle the visibility of the immediate child post list and subcategory list
        let nextElement = toggle.nextElementSibling;
        while (nextElement) {
          if (nextElement.matches('.post-list') || nextElement.matches('.subcategory-list')) {
            nextElement.classList.toggle('expanded');
          }
          nextElement = nextElement.nextElementSibling;
        }
      });
    });
  });
  
  
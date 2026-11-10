document.addEventListener('DOMContentLoaded', function() {
  
  // Target the parent row where the ::before is now located
  const parentRow = document.querySelector('.kb-row-layout-id185_4c2cb3-ab');
  
  if (parentRow) {
    // Add click event to the parent
    parentRow.addEventListener('click', function(e) {
      // Check if click is in the bottom area where "Continue reading" appears
      const rect = parentRow.getBoundingClientRect();
      const clickY = e.clientY - rect.top;
      const containerHeight = rect.height;
      
      // If clicked in bottom 50px (where the text appears)
      if (clickY > containerHeight - 50 && !parentRow.classList.contains('expanded')) {
        toggleContent();
      }
    });
    
    // Function to toggle expanded state
    function toggleContent() {
      parentRow.classList.toggle('expanded');
      
      // Optional: Smooth scroll to top of section when collapsing
      if (!parentRow.classList.contains('expanded')) {
        parentRow.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }
  }
  
});

<script>
document.addEventListener('DOMContentLoaded', function(){
    // 아코디언 토글 기능
    const toggleButtons = document.querySelectorAll('.btn-toggle-services');
    
    toggleButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const staffId = this.getAttribute('data-staff-id');
            const accordionRow = document.querySelector('.service-accordion-' + staffId);
            const toggleIcon = this.querySelector('.toggle-icon');
            
            if (accordionRow) {
                if (accordionRow.style.display === 'none') {
                    accordionRow.style.display = '';
                    toggleIcon.textContent = '▲';
                } else {
                    accordionRow.style.display = 'none';
                    toggleIcon.textContent = '▼';
                }
            }
        });
    });
});
</script>


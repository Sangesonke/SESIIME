document.addEventListener('DOMContentLoaded', function () {
  // Book appointment modal
  // const bookBtn = document.getElementById('book-btn');
  const modal = document.getElementById('book');
  const closeModal = document.querySelector('.close');
  document.getElementById('time').max = "17:00".min = "9:00 ";



  bookBtn.addEventListener('click', function () {
    modal.style.display = 'block';
  });

  closeModal.addEventListener('click', function () {
    modal.style.display = 'none';
  });

  window.addEventListener('click', function (event) {
    if (event.target == modal) {
      modal.style.display = 'none';
    }
  });

  // Form submission
  const form = document.getElementById('appointment-form');

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    // Handle form submission here
  });
});

document.querySelectorAll('.star-rating span').forEach(star => {
  star.addEventListener('click', function () {
    const ratingValue = this.getAttribute('data-value');
    document.getElementById('rating').value = ratingValue;

    // Reset all stars
    document.querySelectorAll('.star-rating span').forEach(s => s.classList.remove('active'));

    // Highlight selected stars
    for (let i = 1; i <= ratingValue; i++) {
    document.querySelector(`.star-rating span[data-value="${i}"]`).classList.add('active');
  }
  });
});

document.getElementById('feedbackForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch('submit_feedback.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    document.getElementById('responseMsg').textContent = data.message;
    if (data.success) {
      this.reset();
      document.querySelectorAll('.star-rating span').forEach(s => s.classList.remove('active'));
    }
  })
  .catch(err => {
    document.getElementById('responseMsg').textContent = "An error occurred. Please try again.";
  });
});
document.addEventListener("DOMContentLoaded", () => {
  fetch("fetch_feedback.php")
    .then((response) => response.json())
    .then((data) => {
      const container = document.getElementById("feedbackCardsContainer");
      data.forEach((feedback) => {
        const card = document.createElement("div");
        card.className = "feedback-card";
        card.innerHTML = `
          <h3>${feedback.customer_name}</h3>
          <p><strong>Product:</strong> ${feedback.product_name}</p>
          <p><strong>Rating:</strong> ${"★".repeat(feedback.rating)}</p>
          <p><strong>Comments:</strong> ${feedback.comments}</p>
          <div class="actions">
            <button onclick="editFeedback(${feedback.id})">Edit</button>
            <button onclick="deleteFeedback(${feedback.id})">Delete</button>
          </div>
        `;
        container.appendChild(card);
      });
    })
    .catch((err) => {
      console.error("Failed to fetch feedback:", err);
    });
});
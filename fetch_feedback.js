// OPEN MODAL  
function openEditModal(id, name, product, rating, comment) {
  document.getElementById("editId").value = id;
  document.getElementById("editName").value = name;
  document.getElementById("editProduct").value = product;
  document.getElementById("editRating").value = rating;
  document.getElementById("editComment").value = comment;

  document.getElementById("editModal").style.display = "block";
}

// CLOSE MODAL
function closeEditModal() {
  document.getElementById("editModal").style.display = "none";
}

//  EDIT BUTTON 
function attachEditButtonEvents() {
  const editButtons = document.querySelectorAll(".edit-btn");
  editButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      openEditModal(
        btn.dataset.id,
        btn.dataset.name,
        btn.dataset.product,
        btn.dataset.rating,
        btn.dataset.comment
      );
    });
  });
}

//  DELETE BUTTON
function attachDeleteButtonEvents() {
  const deleteButtons = document.querySelectorAll(".delete-btn");
  deleteButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      if (confirm("Are you sure you want to delete this feedback?")) {
        fetch("delete_feedback.php", {
        method: "POST",
        headers: {
        "Content-Type": "application/x-www-form-urlencoded"
          },
        body: `id=${id}&_method=DELETE`
        })
          .then((res) => res.text())
          .then((msg) => {
            alert(msg);
            location.reload(); //  Refresh the cards
          })
          .catch((err) => {
            alert("Error deleting feedback.");
            console.error(err);
          });
      }
    });
  });
}






document.addEventListener("DOMContentLoaded", () => {
  // FETCH & SHOW FEEDBACK CARDS
  fetch("fetch_feedback.php")
    .then((response) => response.json())
    .then((data) => {
      console.log("Fetched data:", data);
      const container = document.getElementById("feedbackCardsContainer");
      data.forEach((feedback) => {
        const comment = (feedback.comments || "").replace(/`/g, "'");
        const card = document.createElement("div");
        card.className = "feedback-card";
        card.innerHTML = `
          <h3>${feedback.customer_name}</h3>
          <p><strong>Product:</strong> ${feedback.product_name}</p>
          <p><strong>Rating:</strong> ${feedback.rating} ⭐</p>
          <p><strong>Comment:</strong> ${feedback.comments}</p>
          <div class="card-buttons">
            <button 
              class="edit-btn" 
              data-id="${feedback.id}" 
              data-name="${feedback.customer_name}" 
              data-product="${feedback.product_name}" 
              data-rating="${feedback.rating}" 
              data-comment="${feedback.comments.replace(/`/g, "'")}"
            >Edit</button>
            <button class="delete-btn" data-id="${feedback.id}">Delete</button>
          </div>
        `;
        container.appendChild(card);
      });

      attachEditButtonEvents();
      attachDeleteButtonEvents();
    });

  // HANDLING FORM SUBMISSION
  const editForm = document.getElementById("editForm");
  editForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const id = document.getElementById("editId").value;
    const name = document.getElementById("editName").value;
    const product = document.getElementById("editProduct").value;
    const rating = document.getElementById("editRating").value;
    const comment = document.getElementById("editComment").value;

    console.log("Submitting edited data:", { id, name, product, rating, comment });

    fetch("edit_feedback.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${id}&name=${encodeURIComponent(name)}&product=${encodeURIComponent(product)}&rating=${rating}&comment=${encodeURIComponent(comment)}`
    })
      .then((res) => res.text())
      .then((msg) => {
        console.log("Server response:", msg);
        alert("Feedback updated successfully!");
        closeEditModal();
        location.reload();
      })
      .catch((err) => {
        console.error("Update failed:", err);
        alert("Update failed.");
      });
  });

});

document.addEventListener("DOMContentLoaded", () => {
  const videos = document.querySelectorAll(".video");

  // Autoplay on hover
  videos.forEach((video) => {
    video.addEventListener("mouseover", () => video.play());
    video.addEventListener("mouseout", () => video.pause());
  });

  // Like Button Functionality
  const likeButtons = document.querySelectorAll(".like-button");
  likeButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const count = btn.querySelector("span");
      count.textContent = parseInt(count.textContent) + 1;
    });
  });

  // Placeholder for comment/share functionality
  const commentButtons = document.querySelectorAll(".comment-button");
  const shareButtons = document.querySelectorAll(".share-button");

  commentButtons.forEach((btn) =>
    btn.addEventListener("click", () => alert("Comment feature coming soon!"))
  );
  shareButtons.forEach((btn) =>
    btn.addEventListener("click", () => alert("Share feature coming soon!"))
  );
});

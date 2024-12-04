// Add infinite scroll interaction
const videos = document.querySelectorAll('video');

videos.forEach((video) => {
  video.addEventListener('play', () => {
    videos.forEach((otherVideo) => {
      if (otherVideo !== video) {
        otherVideo.pause();
      }
    });
  });
});

document.querySelectorAll('.sidebar ul li').forEach((item) => {
  item.addEventListener('click', () => alert('Feature is under development!'));
});

document.querySelector('.login-btn').addEventListener('click', () => {
  alert('Login functionality is coming soon!');
});

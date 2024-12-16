// Button Functionality
document.querySelector('.like').addEventListener('click', () => {
    alert('You liked the video!');
});

document.querySelector('.comment').addEventListener('click', () => {
    let comment = prompt('Enter your comment:');
    if (comment) alert(`Your comment: ${comment}`);
});

document.querySelector('.save').addEventListener('click', () => {
    alert('Video saved!');
});

document.querySelector('.dislike').addEventListener('click', () => {
    alert('You disliked the video!');
});

// Placeholder video setup
const video = document.getElementById('videoPlayer');
video.src = "sample-video2.mp4";

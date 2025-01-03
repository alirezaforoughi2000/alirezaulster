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

// Video element
const video = document.getElementById('videoPlayer');

// Fetch video data from the backend API
fetch('http://127.0.0.1:5000/videos')
  .then((response) => response.json())
  .then((data) => {
    console.log('Fetched Data:', data);

    if (data.length > 0) {
      video.src = `http://127.0.0.1:5000${data[0].url}`;
      console.log('Video URL:', video.src);
    } else {
      console.error('No videos available.');
    }

    // Call the function to display videos
    displayVideos(data);
  })
  .catch((error) => {
    console.error('Error fetching data:', error);
  });

// Function to dynamically display videos on the page
function displayVideos(videos) {
  const videoContainer = document.getElementById('video-container');

  // Clear any existing content
  videoContainer.innerHTML = '';

  // Loop through the videos and create elements
  videos.forEach((video) => {
    const videoElement = document.createElement('div');
    videoElement.className = 'video-item';

    videoElement.innerHTML = `
      <h3>${video.title}</h3>
      <p>${video.description}</p>
      <a href="http://127.0.0.1:5000${video.url}" target="_blank">Watch Video</a>
    `;

    // Append the video element to the container
    videoContainer.appendChild(videoElement);
  });
}
// Handle Video Upload
document.getElementById('uploadForm').onsubmit = async function(event) {
    event.preventDefault();

    const fileInput = document.getElementById('videoFile');
    const file = fileInput.files[0];
    const uploadMessage = document.getElementById('uploadMessage');

    if (!file) {
        alert('Please select a file.');
        return;
    }

    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await fetch('http://127.0.0.1:8000/upload', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (response.ok) {
            uploadMessage.innerHTML = `✅ ${data.message}`;
            fileInput.value = '';  // Clear the file input
            fetchVideos();  // Refresh video list after upload
        } else {
            uploadMessage.innerHTML = `❌ ${data.detail}`;
        }
    } catch (error) {
        uploadMessage.innerHTML = '❌ Error uploading video.';
        console.error('Upload error:', error);
    }
};

/**
 * Account Settings - Account
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    const deactivateAcc = document.querySelector('#formAccountDeactivation');

    // Update/reset user image of account page
    let accountUserImage = document.getElementById('uploadedAvatar');
    const fileInput = document.querySelector('.account-file-input'),
      resetFileInput = document.querySelector('.account-image-reset');

    if (accountUserImage) {
      const resetImage = accountUserImage.src;
      fileInput.onchange = () => {
        if (fileInput.files[0]) {
          accountUserImage.classList.remove('d-none');
          accountUserImage.src = window.URL.createObjectURL(fileInput.files[0]);
        }
      };
    //   resetFileInput.onclick = () => {
    //     fileInput.value = '';
    //     accountUserImage.src = resetImage;
    //   };
    }
  })();
  
  
  (function () {
    // Update/reset user image of account page
    let fullScreenImage = document.getElementById('fullScreenImage');
    const uploadScreenImage = document.querySelector('.uploadScreenImage');
    // resetFileInput = document.querySelector('.account-image-reset');
    
    if (fullScreenImage) {
      const resetImage = fullScreenImage.src;
      uploadScreenImage.onchange = () => {
        if (uploadScreenImage.files[0]) {
          fullScreenImage.classList.remove('d-none');
          fullScreenImage.src = window.URL.createObjectURL(uploadScreenImage.files[0]);
        }
      };
    //   resetFileInput.onclick = () => {
    //     fileInput.value = '';
    //     fullScreenImage.src = resetImage;
    //   };
    }
  })();
});

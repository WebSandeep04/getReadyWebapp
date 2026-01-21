document.addEventListener('DOMContentLoaded', function () {
  const carousel = document.querySelector('.carousel-items');
  const nextBtn = document.querySelector('.next');
  const prevBtn = document.querySelector('.prev');

  if (nextBtn && prevBtn && carousel) {
    nextBtn.addEventListener('click', () => {
      carousel.scrollBy({ left: 220, behavior: 'smooth' });
    });

    prevBtn.addEventListener('click', () => {
      carousel.scrollBy({ left: -220, behavior: 'smooth' });
    });
  }

  const mainImage = document.querySelector('#activeProductImage');
  const thumbs = document.querySelectorAll('.product-gallery__thumbs .thumb');

  if (mainImage && thumbs.length) {
    thumbs.forEach((thumb) => {
      thumb.addEventListener('click', () => {
        const newImage = thumb.getAttribute('data-image');
        if (newImage) {
          mainImage.classList.add('fade-out');
          setTimeout(() => {
            mainImage.src = newImage;
            mainImage.classList.remove('fade-out');
            thumbs.forEach(btn => btn.classList.remove('thumb-active'));
            thumb.classList.add('thumb-active');
          }, 150);
        }
      });
    });
  }
});

// Reply System Functionality
$(document).ready(function () {
  // Toggle Reply Form
  $(document).on('click', '.toggle-reply-form', function () {
    $(this).next('.reply-form').slideToggle();
  });

  // Submit Reply
  $(document).on('submit', '.reply-form', function (e) {
    e.preventDefault();
    const $form = $(this);
    const type = $form.data('type'); // 'question' or 'review'
    const id = $form.data('id');
    const $input = $form.find('input[name="message"]');
    const message = $input.val();
    const $btn = $form.find('button[type="submit"]');

    if (!message.trim()) return;

    $btn.prop('disabled', true).text('Posting...');

    let url = '';
    if (type === 'question') {
      url = `/questions/${id}/reply`;
    } else if (type === 'review') {
      url = `/reviews/${id}/reply`;
    }

    $.ajax({
      url: url,
      type: 'POST',
      data: {
        message: message,
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        if (response.success) {
          $input.val('');
          $form.slideUp();

          // Create new reply HTML
          const reply = response.reply;
          const replyHtml = `
                        <div class="reply-item mb-2 bg-light p-2 rounded" data-reply-id="${reply.id}">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>${reply.user.name}</strong>
                                    <span class="text-muted small ms-2">Just now</span>
                                    <p class="mb-0 small mt-1">${reply.message}</p>
                                </div>
                                <button class="btn btn-sm btn-link text-danger delete-reply-btn p-0" data-reply-id="${reply.id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;

          // Prepend to replies list
          $form.prev('.toggle-reply-form').before(replyHtml);

          if (typeof showAlert === 'function') {
            showAlert('success', 'Reply posted successfully!');
          } else {
            alert('Reply posted successfully!');
          }
        }
      },
      error: function (xhr) {
        if (xhr.status === 401) {
          window.location.href = '/login';
        } else {
          if (typeof showAlert === 'function') {
            showAlert('danger', 'Failed to post reply. Please try again.');
          } else {
            alert('Failed to post reply.');
          }
        }
      },
      complete: function () {
        $btn.prop('disabled', false).text('Post');
      }
    });
  });

  // Delete Reply
  $(document).on('click', '.delete-reply-btn', function () {
    if (!confirm('Are you sure you want to delete this reply?')) return;

    const $btn = $(this);
    const replyId = $btn.data('reply-id');
    const $item = $btn.closest('.reply-item');

    $.ajax({
      url: `/replies/${replyId}`,
      type: 'DELETE',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        if (response.success) {
          $item.fadeOut(function () {
            $(this).remove();
          });
          if (typeof showAlert === 'function') {
            showAlert('success', 'Reply deleted successfully');
          }
        }
      },
      error: function () {
        if (typeof showAlert === 'function') {
          showAlert('danger', 'Failed to delete reply.');
        } else {
          alert('Failed to delete reply.');
        }
      }
    });
  });
});
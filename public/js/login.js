$(document).ready(function () {
    const loginUrl = (window.AuthConfig && window.AuthConfig.routes && window.AuthConfig.routes.login) || '/login';

    // Send OTP for mobile login
    $('#sendLoginOtpBtn').on('click', function () {
        const phone = $('#loginPhone').val().trim();

        if (!phone || !/^[0-9]{10,15}$/.test(phone)) {
            showError('loginPhoneError', 'Please enter a valid mobile number (10-15 digits)');
            return;
        }

        hideError('loginPhoneError');

        // Disable button
        $(this).prop('disabled', true).text('Sending...');

        const redirectValue = $('#mobileLoginForm input[name="redirect"]').val();

        $.ajax({
            url: loginUrl,
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                login_type: 'mobile',
                phone: phone,
                send_otp: true,
                redirect: redirectValue
            },
            success: function (response) {
                if (response.success) {
                    if (response.otp) {
                        alert(`Your login OTP is ${response.otp}`);
                    }
                    // Show OTP section
                    $('#mobileLoginStep1').hide();
                    $('#mobileLoginStep2').addClass('show');

                    // Start timer
                    startOtpTimer('loginOtpTimer', 'resendLoginOtpBtn', function () {
                        $('#sendLoginOtpBtn').prop('disabled', false).text('Send OTP');
                    });

                } else {
                    showError('loginPhoneError', response.message || 'Failed to send OTP');
                    $('#sendLoginOtpBtn').prop('disabled', false).text('Send OTP');
                }
            },
            error: function (xhr) {
                const error = xhr.responseJSON;
                if (error && error.errors) {
                    showError('loginPhoneError', Object.values(error.errors)[0][0]);
                } else {
                    showError('loginPhoneError', error?.message || 'Failed to send OTP. Please try again.');
                }
                $('#sendLoginOtpBtn').prop('disabled', false).text('Send OTP');
            }
        });
    });

    // Resend OTP
    $('#resendLoginOtpBtn').on('click', function () {
        $('#sendLoginOtpBtn').trigger('click');
    });

    // Handle Business Type change
    $('#is_gst').on('change', function () {
        if ($(this).val() === '1') {
            $('#gstinGroup').show();
            $('#gstin').prop('required', true);
        } else {
            $('#gstinGroup').hide();
            $('#gstin').prop('required', false);
        }
    });

    // Mobile login form submission
    $('#mobileLoginForm').on('submit', function (e) {
        e.preventDefault();

        const phone = $('#loginPhone').val().trim();
        const redirectValue = $('#mobileLoginForm input[name="redirect"]').val();
        const submitBtn = $(this).find('button[type="submit"]:visible');

        // Check if we are in Step 3 (Registration)
        if ($('#mobileLoginStep3').is(':visible')) {
            // Validate Profile Details
            const city = $('#city').val().trim();
            const age = $('#age').val();
            const gender = $('#gender').val();
            const isGst = $('#is_gst').val();
            const gstin = $('#gstin').val().trim();
            const verificationToken = $('#verificationToken').val();

            if (!city || !age || !gender) {
                alert('Please fill all required fields');
                return;
            }

            if (isGst === '1' && !gstin) {
                alert('Please enter GSTIN');
                return;
            }

            // Disable submit button
            submitBtn.prop('disabled', true).text('Creating Account...');

            // Call Register API
            $.ajax({
                url: '/complete-registration',
                method: 'POST',
                data: {
                    _token: $('input[name="_token"]').val(),
                    phone: phone,
                    verification_token: verificationToken,
                    city: city,
                    age: age,
                    gender: gender,
                    is_gst: isGst,
                    gstin: gstin
                },
                success: function (response) {
                    if (response.success) {
                        window.location.href = response.redirect || '/';
                    } else {
                        alert(response.message || 'Registration failed');
                        submitBtn.prop('disabled', false).text('Complete Registration');
                    }
                },
                error: function (xhr) {
                    const error = xhr.responseJSON;
                    alert(error?.message || 'Registration failed');
                    submitBtn.prop('disabled', false).text('Complete Registration');
                }
            });

        } else {
            // Step 2: Verify OTP
            const otp = $('#loginOtp').val().trim();

            if (!otp || !/^[0-9]{6}$/.test(otp)) {
                showError('loginOtpError', 'Please enter a valid 6-digit OTP');
                return;
            }

            hideError('loginOtpError');

            // Disable submit button
            submitBtn.prop('disabled', true).text('Verifying...');

            $.ajax({
                url: loginUrl,
                method: 'POST',
                data: {
                    _token: $('input[name="_token"]').val(),
                    login_type: 'mobile',
                    phone: phone,
                    otp: otp,
                    redirect: redirectValue,
                    remember: $('#rememberMeMobile').is(':checked')
                },
                success: function (response) {
                    if (response.success) {
                        if (response.is_new_user) {
                            // Switch to Step 3
                            $('#mobileLoginStep2').removeClass('show').hide();
                            $('#mobileLoginStep3').show();
                            $('#verificationToken').val(response.verification_token);
                            submitBtn.prop('disabled', false).text('Verify OTP'); // Reset previous button (though hidden)
                        } else {
                            // Redirect to home
                            window.location.href = response.redirect || '/';
                        }
                    } else {
                        showError('loginOtpError', response.message || 'Invalid OTP');
                        submitBtn.prop('disabled', false).text('Verify OTP');
                    }
                },
                error: function (xhr) {
                    const error = xhr.responseJSON;
                    if (error && error.errors) {
                        const firstError = Object.values(error.errors)[0];
                        showError('loginOtpError', Array.isArray(firstError) ? firstError[0] : firstError);
                    } else {
                        showError('loginOtpError', error?.message || 'Invalid or expired OTP');
                    }
                    submitBtn.prop('disabled', false).text('Verify OTP');
                }
            });
        }
    });

    // OTP input - only numbers
    $('#loginOtp').on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Helper functions
    function showError(elementId, message) {
        $('#' + elementId).text(message).show();
    }

    function hideError(elementId) {
        $('#' + elementId).hide();
    }

    function resetMobileLoginForm() {
        $('#mobileLoginStep1').show();
        $('#mobileLoginStep2').removeClass('show');
        $('#loginPhone').val('');
        $('#loginOtp').val('');
        $('#loginPhoneError').hide();
        $('#loginOtpError').hide();
        $('#sendLoginOtpBtn').prop('disabled', false).text('Send OTP');
        clearInterval(window.loginOtpTimerInterval);
        $('#loginOtpTimer').text('');
        $('#resendLoginOtpBtn').hide();
    }

    function startOtpTimer(timerElementId, resendBtnId, onComplete) {
        let timeLeft = 60; // 60 seconds
        const timerElement = $('#' + timerElementId);
        const resendBtn = $('#' + resendBtnId);

        timerElement.text(`Resend OTP in ${timeLeft} seconds`);
        resendBtn.hide();

        window.loginOtpTimerInterval = setInterval(function () {
            timeLeft--;
            if (timeLeft > 0) {
                timerElement.text(`Resend OTP in ${timeLeft} seconds`);
            } else {
                clearInterval(window.loginOtpTimerInterval);
                timerElement.text('');
                resendBtn.show();
                if (onComplete) onComplete();
            }
        }, 1000);
    }
});


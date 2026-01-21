$(document).ready(function () {
    const registerUrl = (window.AuthConfig && window.AuthConfig.routes && window.AuthConfig.routes.register) || '/register';

    // Send OTP for mobile signup
    $('#sendSignupOtpBtn').on('click', function () {
        const phone = $('#signupPhone').val().trim();

        hideAllErrors();

        if (!phone || !/^[0-9]{10,15}$/.test(phone)) {
            showError('signupPhoneError', 'Please enter a valid mobile number (10-15 digits)');
            return;
        }

        // Disable button
        $(this).prop('disabled', true).text('Sending...');

        $.ajax({
            url: registerUrl,
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                signup_type: 'mobile',
                phone: phone,
                send_otp: true
            },
            success: function (response) {
                if (response.success) {
                    if (response.otp) {
                        alert(`Your signup OTP is ${response.otp}`);
                    }
                    // Show OTP section
                    $('#mobileSignupStep1').hide();
                    $('#mobileSignupStep2').addClass('show');

                    // Start timer
                    startOtpTimer('signupOtpTimer', 'resendSignupOtpBtn', function () {
                        $('#sendSignupOtpBtn').prop('disabled', false).text('Send OTP');
                    });

                } else {
                    showError('signupPhoneError', response.message || 'Failed to send OTP');
                    $('#sendSignupOtpBtn').prop('disabled', false).text('Send OTP');
                }
            },
            error: function (xhr) {
                const error = xhr.responseJSON;
                if (error && error.errors) {
                    // Show first error
                    const firstErrorKey = Object.keys(error.errors)[0];
                    const firstErrorMsg = error.errors[firstErrorKey][0];
                    showError('signup' + firstErrorKey.charAt(0).toUpperCase() + firstErrorKey.slice(1) + 'Error', firstErrorMsg);
                } else {
                    showError('signupPhoneError', error?.message || 'Failed to send OTP. Please try again.');
                }
                $('#sendSignupOtpBtn').prop('disabled', false).text('Send OTP');
            }
        });
    });

    // Resend OTP
    $('#resendSignupOtpBtn').on('click', function () {
        $('#sendSignupOtpBtn').trigger('click');
    });

    // Step 2: Verify OTP button click
    $('#verifyOtpBtn').on('click', function () {
        const phone = $('#signupPhone').val().trim();
        const otp = $('#signupOtp').val().trim();

        // Clear previous errors
        hideError('signupOtpError');

        if (!otp || !/^[0-9]{6}$/.test(otp)) {
            showError('signupOtpError', 'Please enter a valid 6-digit OTP');
            return;
        }

        // Disable button
        const verifyBtn = $(this);
        verifyBtn.prop('disabled', true).text('Verifying...');

        $.ajax({
            url: registerUrl,
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                signup_type: 'mobile',
                phone: phone,
                otp: otp,
                verify_otp: true
            },
            success: function (response) {
                if (response.success && response.verification_token) {
                    // Store verification token
                    $('#verificationToken').val(response.verification_token);

                    // Hide Step 2, show Step 3
                    $('#mobileSignupStep2').removeClass('show');
                    $('#mobileSignupStep3').addClass('show');
                } else {
                    showError('signupOtpError', response.message || 'OTP verification failed');
                    verifyBtn.prop('disabled', false).text('Verify OTP');
                }
            },
            error: function (xhr) {
                const error = xhr.responseJSON;
                if (error && error.errors) {
                    const firstErrorKey = Object.keys(error.errors)[0];
                    let errorElementId = firstErrorKey === 'phone' ? 'signupPhoneError' : 'signupOtpError';
                    const firstErrorMsg = Array.isArray(error.errors[firstErrorKey])
                        ? error.errors[firstErrorKey][0]
                        : error.errors[firstErrorKey];
                    showError(errorElementId, firstErrorMsg);
                } else {
                    showError('signupOtpError', error?.message || 'Invalid or expired OTP');
                }
                verifyBtn.prop('disabled', false).text('Verify OTP');
            }
        });
    });

    // Mobile signup form submission (Step 3: Create account)
    $('#mobileSignupForm').on('submit', function (e) {
        e.preventDefault();

        // Only handle if we're on Step 3
        if (!$('#mobileSignupStep3').hasClass('show')) {
            return;
        }

        const phone = $('#signupPhone').val().trim();
        const verificationToken = $('#verificationToken').val();
        const city = $('#signupCity').val().trim();
        const age = $('#signupAge').val().trim();
        const gender = $('#signupGender').val();
        const is_gst = $('#signupIsGst').val();
        const gstin = $('#signupGstin').val().trim();

        // Clear previous errors
        hideError('signupCityError');
        hideError('signupAgeError');
        hideError('signupGenderError');
        hideError('signupIsGstError');
        hideError('signupGstinError');

        let hasError = false;

        if (!city) {
            showError('signupCityError', 'Please enter your city');
            hasError = true;
        }

        if (!age || isNaN(age) || parseInt(age) < 1 || parseInt(age) > 120) {
            showError('signupAgeError', 'Please enter a valid age (1-120)');
            hasError = true;
        }

        if (!gender) {
            showError('signupGenderError', 'Please select your gender');
            hasError = true;
        }

        if (!is_gst) {
            showError('signupIsGstError', 'Please select a business type');
            hasError = true;
        } else if (is_gst === '1') {
            if (!gstin || !/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/.test(gstin)) {
                showError('signupGstinError', 'Please enter a valid 15-digit GSTIN');
                hasError = true;
            }
        }

        if (hasError) {
            return;
        }

        // Disable submit button
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).text('Creating Account...');

        $.ajax({
            url: registerUrl,
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                signup_type: 'mobile',
                phone: phone,
                verification_token: verificationToken,
                city: city,
                age: age,
                gender: gender,
                is_gst: is_gst,
                gstin: gstin
            },
            success: function (response) {
                if (response.success) {
                    // Redirect to home
                    window.location.href = response.redirect || '/';
                } else {
                    showError('signupCityError', response.message || 'Failed to create account');
                    submitBtn.prop('disabled', false).text('Create Account');
                }
            },
            error: function (xhr) {
                const error = xhr.responseJSON;
                if (error && error.errors) {
                    // Show errors for each field
                    Object.keys(error.errors).forEach(function (key) {
                        const errorElementId = 'signup' + key.charAt(0).toUpperCase() + key.slice(1) + 'Error';
                        const errorMsg = Array.isArray(error.errors[key])
                            ? error.errors[key][0]
                            : error.errors[key];
                        showError(errorElementId, errorMsg);
                    });
                } else {
                    showError('signupCityError', error?.message || 'Failed to create account. Please try again.');
                }
                submitBtn.prop('disabled', false).text('Create Account');
            }
        });
    });

    // OTP input - only numbers
    $('#signupOtp').on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Phone input - only numbers
    $('#signupPhone').on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Age input - only numbers
    $('#signupAge').on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Helper functions
    function showError(elementId, message) {
        $('#' + elementId).text(message).show();
    }

    function hideError(elementId) {
        $('#' + elementId).hide();
    }

    function hideAllErrors() {
        hideError('signupPhoneError');
        hideError('signupOtpError');
        hideError('signupCityError');
        hideError('signupAgeError');
        hideError('signupGenderError');
    }

    function resetMobileSignupForm() {
        $('#mobileSignupStep1').show();
        $('#mobileSignupStep2').removeClass('show');
        $('#mobileSignupStep3').removeClass('show');
        $('#signupPhone').val('');
        $('#signupOtp').val('');
        $('#signupCity').val('');
        $('#signupAge').val('');
        $('#signupGender').val('');
        $('#verificationToken').val('');
        hideAllErrors();
        $('#sendSignupOtpBtn').prop('disabled', false).text('Send OTP');
        $('#verifyOtpBtn').prop('disabled', false).text('Verify OTP');
        clearInterval(window.signupOtpTimerInterval);
        $('#signupOtpTimer').text('');
        $('#resendSignupOtpBtn').hide();
    }

    function startOtpTimer(timerElementId, resendBtnId, onComplete) {
        let timeLeft = 60; // 60 seconds
        const timerElement = $('#' + timerElementId);
        const resendBtn = $('#' + resendBtnId);

        timerElement.text(`Resend OTP in ${timeLeft} seconds`);
        resendBtn.hide();

        window.signupOtpTimerInterval = setInterval(function () {
            timeLeft--;
            if (timeLeft > 0) {
                timerElement.text(`Resend OTP in ${timeLeft} seconds`);
            } else {
                clearInterval(window.signupOtpTimerInterval);
                timerElement.text('');
                resendBtn.show();
                if (onComplete) onComplete();
            }
        }, 1000);
    }
});


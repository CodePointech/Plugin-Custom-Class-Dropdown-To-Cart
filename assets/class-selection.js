jQuery(document).ready(function ($) {
    function initializeCartScript() {
        var proceedButton = $('.checkout-button'); // Adjust the selector if necessary

        // Set initial selected class from localStorage
        var initialClass = localStorage.getItem('selected_class');
        if (initialClass) {
            $('#class-selection').val(initialClass); // Set the dropdown value if it exists in localStorage
        }

        // When the class selection dropdown value changes
        $('#class-selection').off('change').on('change', function () {
            var selectedClass = $(this).val();

            if (selectedClass) {
                // Remove the highlight if a class is selected
                $('#class-selection').removeClass('highlight-dropdown');

                // Store the selected class in localStorage
                localStorage.setItem('selected_class', selectedClass);

                // Send AJAX request to save the selected class in the cart
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    data: {
                        action: 'save_class_selection',
                        selected_class: selectedClass
                    },
                    success: function (response) {
                        if (!response.success) {
                            alert('Failed to save the selected class. Please try again.');
                        }
                    },
                    error: function () {
                        alert('An error occurred. Please try again.');
                    }
                });
            } else {


                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    data: {
                        action: 'save_class_selection',
                        selected_class: selectedClass
                    },
                    success: function (response) {
                        if (!response.success) {
                            alert('Failed to save the selected class. Please try again.');
                        }
                    },
                    error: function () {
                        alert('An error occurred. Please try again.');
                    }
                });


                // Add the highlight back if no class is selected
                $('#class-selection').addClass('highlight-dropdown');

                // Clear the stored class in localStorage if none is selected
                localStorage.removeItem('selected_class');
            }
        });

        // Prevent checkout if class is not selected
        proceedButton.off('click').on('click', function (e) {
            var selectedClass = $('#class-selection').val();

            if (!selectedClass) {
                // Highlight the dropdown and prevent checkout
                $('#class-selection').addClass('highlight-dropdown');
                e.preventDefault(); // Prevent the default action of the button
            }
        });
    }

    // Initialize the script on page load
    initializeCartScript();

    // Reinitialize the script after WooCommerce updates cart totals
    $(document.body).on('updated_cart_totals', function () {
        initializeCartScript();
    });

    // Clear localStorage on successful checkout
    $(document.body).on('checkout_place_order', function () {
        localStorage.removeItem('selected_class'); // Clear stored class on checkout
    });
});

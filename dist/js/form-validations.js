    // admin-Customer Form Validations

    function validateEditForm() {
        const name = document.getElementById('edit-customer-name').value.trim();
        const email = document.getElementById('edit-customer-email').value.trim();
        const username = document.getElementById('edit-customer-username').value.trim();
        const address = document.getElementById('edit-customer-address').value.trim();
        const namePattern = /^[A-Za-z\s]+$/;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        let isValid = true;

        if (!namePattern.test(name)) {
            document.getElementById('name-msg').innerText = "Invalid name format.";
            document.getElementById('name-msg').style.color = 'red';
            isValid = false;
        } else {
            document.getElementById('name-msg').innerText = "";
        }

        if (!emailPattern.test(email)) {
            document.getElementById('email-msg').innerText = "Invalid email format.";
            document.getElementById('email-msg').style.color = 'red';
            isValid = false;
        } else {
            document.getElementById('email-msg').innerText = "";
        }

        if (username === '') {
            document.getElementById('username-msg').innerText = "Username cannot be empty.";
            document.getElementById('username-msg').style.color = 'red';
            isValid = false;
        } else {
            document.getElementById('username-msg').innerText = "";
        }

        if (address === '') {
            document.getElementById('address-msg').innerText = "Address cannot be empty.";
            document.getElementById('address-msg').style.color = 'red';
            isValid = false;
        } else {
            document.getElementById('address-msg').innerText = "";
        }

        return isValid;
    }

    document.getElementById('edit-form').addEventListener('submit', function(event) {
        if (!validateEditForm()) {
            event.preventDefault(); // Prevent form submission if validation fails
        }
    });

// admin-Product Form Validations
document.addEventListener('DOMContentLoaded', function() {
    function validateAddProductForm() {
        const productName = document.getElementById('product_name').value.trim();
        const category = document.getElementById('category').value.trim();
        const stockQuantity = document.getElementById('stockQuantity').value.trim();
        const price = document.getElementById('price').value.trim();
        const productImage = document.getElementById('input-file').files[0];
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

        let isValid = true;

        // Product Name validation
        if (productName === '') {
            document.getElementById('product-name-msg').innerText = "Product Name is required.";
            document.getElementById('product-name-msg').style.color = 'red';
            isValid = false;
        } else {
            document.getElementById('product-name-msg').innerText = "";
        }

        // Category validation
        if (category === '') {
            document.getElementById('category-msg').innerText = "Category is required.";
            document.getElementById('category-msg').style.color = 'red';
            isValid = false;
        } else {
            document.getElementById('category-msg').innerText = "";
        }

        // Stock Quantity validation
        if (stockQuantity === '' || isNaN(stockQuantity) || stockQuantity <= 0) {
            document.getElementById('stock-quantity-msg').innerText = "Stock Quantity must be a positive number.";
            document.getElementById('stock-quantity-msg').style.color = 'red';
            isValid = false;
        } else {
            document.getElementById('stock-quantity-msg').innerText = "";
        }

        // Price validation
        if (price === '' || isNaN(price) || price <= 0) {
            document.getElementById('price-msg').innerText = "Price must be a positive number.";
            document.getElementById('price-msg').style.color = 'red';
            isValid = false;
        } else {
            document.getElementById('price-msg').innerText = "";
        }

        // Product Image validation
        if (!productImage) {
            document.getElementById('product-image-msg').innerText = "Product Image is required.";
            document.getElementById('product-image-msg').style.color = 'red';
            isValid = false;
        } else if (!allowedTypes.includes(productImage.type)) {
            document.getElementById('product-image-msg').innerText = "Only JPEG, JPG, or PNG images are allowed.";
            document.getElementById('product-image-msg').style.color = 'red';
            isValid = false;
        } else {
            document.getElementById('product-image-msg').innerText = "";
        }

        return isValid;
    }

    const addProductForm = document.getElementById('add-product-form');
    if (addProductForm) {
        addProductForm.addEventListener('submit', function(event) {
            if (!validateAddProductForm()) {
                event.preventDefault(); // Prevent form submission if validation fails
            }
        });
    }
});


document.addEventListener('DOMContentLoaded', function() {
    // Edit Product Form Validation
    const editProductForm = document.getElementById('edit-form');
    if (editProductForm) {
        editProductForm.addEventListener('submit', function(event) {
            const productName = document.getElementById('edit-product_name').value.trim();
            const category = document.getElementById('edit-category').value.trim();
            const stockQuantity = document.getElementById('edit-stockQuantity').value.trim();
            const price = document.getElementById('edit-price').value.trim();

            if (productName === '') {
                alert('Product Name is required.');
                event.preventDefault();
                return;
            }

            if (category === '') {
                alert('Category is required.');
                event.preventDefault();
                return;
            }

            if (stockQuantity === '' || isNaN(stockQuantity) || stockQuantity <= 0) {
                alert('Stock Quantity must be a positive number.');
                event.preventDefault();
                return;
            }

            if (price === '' || isNaN(price) || price <= 0) {
                alert('Price must be a positive number.');
                event.preventDefault();
                return;
            }
        });
    }
});

function validateAddSupplierForm() {
    const supplierName = document.getElementById('supplier_name').value.trim();
    const contactName = document.getElementById('contact_name').value.trim();
    const contactEmail = document.getElementById('contact_email').value.trim();
    const phoneNumber = document.getElementById('phone_number').value.trim();
    const address = document.getElementById('address').value.trim();

    let isValid = true;

    // Supplier Name
    if (supplierName === '') {
        showError('supplier-name-msg', 'Supplier name is required.');
        isValid = false;
    } else {
        clearError('supplier-name-msg');
    }

    // Contact Name
    if (contactName === '') {
        showError('contact-name-msg', 'Contact name is required.');
        isValid = false;
    } else {
        clearError('contact-name-msg');
    }

    // Contact Email
    if (contactEmail === '') {
        showError('contact-email-msg', 'Contact email is required.');
        isValid = false;
    } else if (!/\S+@\S+\.\S+/.test(contactEmail)) {
        showError('contact-email-msg', 'Invalid email format.');
        isValid = false;
    } else {
        clearError('contact-email-msg');
    }

    // Phone Number
    if (phoneNumber === '') {
        showError('phone-number-msg', 'Phone number is required.');
        isValid = false;
    } else {
        clearError('phone-number-msg');
    }

    // Address
    if (address === '') {
        showError('address-msg', 'Address is required.');
        isValid = false;
    } else {
        clearError('address-msg');
    }

    return isValid;
}

function validateEditSupplierForm() {
    const supplierName = document.getElementById('edit_supplier_name').value.trim();
    const contactName = document.getElementById('edit_contact_name').value.trim();
    const contactEmail = document.getElementById('edit_contact_email').value.trim();
    const phoneNumber = document.getElementById('edit_phone_number').value.trim();
    const address = document.getElementById('edit_address').value.trim();

    let isValid = true;

    // Supplier Name
    if (supplierName === '') {
        showError('edit-supplier-name-msg', 'Supplier name is required.');
        isValid = false;
    } else {
        clearError('edit-supplier-name-msg');
    }

    // Contact Name
    if (contactName === '') {
        showError('edit-contact-name-msg', 'Contact name is required.');
        isValid = false;
    } else {
        clearError('edit-contact-name-msg');
    }

    // Contact Email
    if (contactEmail === '') {
        showError('edit-contact-email-msg', 'Contact email is required.');
        isValid = false;
    } else if (!/\S+@\S+\.\S+/.test(contactEmail)) {
        showError('edit-contact-email-msg', 'Invalid email format.');
        isValid = false;
    } else {
        clearError('edit-contact-email-msg');
    }

    // Phone Number
    if (phoneNumber === '') {
        showError('edit-phone-number-msg', 'Phone number is required.');
        isValid = false;
    } else {
        clearError('edit-phone-number-msg');
    }

    // Address
    if (address === '') {
        showError('edit-address-msg', 'Address is required.');
        isValid = false;
    } else {
        clearError('edit-address-msg');
    }

    return isValid;
}

function showError(elementId, message) {
    const element = document.getElementById(elementId);
    element.textContent = message;
    element.style.color = '#e74c3c'; // Red color for error messages
    element.style.fontSize = '0.875rem'; // Slightly smaller font size
    element.style.marginTop = '0.25rem'; // Space above the message
    element.style.fontWeight = '400'; // Normal font weight
}

function clearError(elementId) {
    const element = document.getElementById(elementId);
    element.textContent = '';
    element.style.color = ''; // Remove color
    element.style.fontSize = ''; // Reset font size
    element.style.marginTop = ''; // Remove margin
    element.style.fontWeight = ''; // Reset font weight
}

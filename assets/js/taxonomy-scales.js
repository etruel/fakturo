jQuery(document).ready(function() {
    jQuery('#term_meta_seller').select2();
    jQuery('#term_meta_category').select2();

    const typeSelect = document.getElementById("term_meta_type");
    const button = document.getElementById("submit");
    const sellerContainer = document.getElementById("scale-seller-div") || document.querySelector('tr:has(#term_meta_seller)');
    const categoryContainer = document.getElementById("scale-category-div") || document.querySelector('tr:has(#term_meta_category)');
    
    function checkIfBaseScaleExists() {
        const tableRows = document.querySelectorAll('#the-list tr');
        return Array.from(tableRows).some(row => {
            const scaleTypeCell = row.querySelector('.column-type');
            return scaleTypeCell && scaleTypeCell.textContent.trim() === 'Base Scale';
        });
    }

    function handleScaleTypeValidation() {
        const submitButton = document.querySelector('input[type="submit"]');
        if (!submitButton) return;

        const baseScaleExists = checkIfBaseScaleExists();
        
        if (baseScaleExists ) {
            // Deshabilitar la opción "base" en el select
            const baseOption = typeSelect.querySelector('option[value="base"]');
            if (baseOption) {
                baseOption.disabled = true;
            }
            
            // Si está seleccionado "base", cambiar a "exception" y deshabilitar botón
            if (typeSelect.value === 'base') {
                typeSelect.value = 'exception';
                submitButton.disabled = true; // Deshabilita el botón
            }
            
            // Mostrar mensaje de advertencia
            let messageDiv = document.getElementById('base-scale-warning');
            if (!messageDiv) {
                messageDiv = document.createElement('div');
                messageDiv.id = 'base-scale-warning';
                messageDiv.className = 'error';
                messageDiv.style.color = 'red';
                messageDiv.textContent = 'A Base Scale already exists. Only one Base Scale is allowed.';
                typeSelect.parentNode.appendChild(messageDiv);
            }
        } else {
            // Habilitar la opción "base" si no existe Base Scale
            const baseOption = typeSelect.querySelector('option[value="base"]');
            if (baseOption) {
                baseOption.disabled = false;
            }
            
            // Habilitar botón de envío
            submitButton.disabled = false;
            
            // Remover mensaje si existe
            const messageDiv = document.getElementById('base-scale-warning');
            if (messageDiv) {
                messageDiv.remove();
            }
        }
    }

    function toggleFields() {
        const isBaseScale = typeSelect.value === "base";
        
        if (sellerContainer) {
            sellerContainer.style.display = isBaseScale ? 'none' : '';
        }
        
        if (categoryContainer) {
            categoryContainer.style.display = isBaseScale ? 'none' : '';
        }
        
        // Si no es "base", mostrar los campos de vendedor y categoría cuando se selecciona "exception"
        if (typeSelect.value === "exception") {
            if (sellerContainer) {
                sellerContainer.style.display = '';
            }
            if (categoryContainer) {
                categoryContainer.style.display = '';
            }
        }
    }
    
    // Estado inicial
    toggleFields();
    handleScaleTypeValidation();
   
    // Event listener para cambios
    typeSelect.addEventListener("change", function() {
        toggleFields();
        handleScaleTypeValidation();
    });

    // Observer para detectar cambios en la tabla
    const tableObserver = new MutationObserver(function() {
        handleScaleTypeValidation();
        toggleFields();
    });

    const table = document.getElementById('the-list');
    if (table) {
        toggleFields();
        tableObserver.observe(table, { childList: true, subtree: true });
    }
    
});

jQuery(document).ready(function($) {
    'use strict';
    
    let currentPage = 1;
    let perPage = 25;
    let currentSort = 'ID';
    let currentSortOrder = 'DESC';
    let currentFilters = {
        search: '',
        categoria: '',
        local: ''
    };
    
    // Initialize
    loadInscriptions();
    loadFilterOptions();
    
    // Event listeners
    $('#general-search').on('input', function() {
        currentFilters.search = $(this).val();
        currentPage = 1;
        loadInscriptions();
    });
    
    $('#categoria-filter').on('change', function() {
        currentFilters.categoria = $(this).val();
        currentPage = 1;
        loadInscriptions();
    });
    
    $('#local-filter').on('change', function() {
        currentFilters.local = $(this).val();
        currentPage = 1;
        loadInscriptions();
    });
    
    
    $('#records-per-page').on('change', function() {
        perPage = parseInt($(this).val());
        currentPage = 1;
        loadInscriptions();
    });
    
    // Pagination
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        currentPage = parseInt($(this).data('page'));
        loadInscriptions();
    });
    
    // Sorting
    $(document).on('click', 'th[data-sort]', function() {
        const sortField = $(this).data('sort');
        if (currentSort === sortField) {
            currentSortOrder = currentSortOrder === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentSort = sortField;
            currentSortOrder = 'ASC';
        }
        loadInscriptions();
    });
    
    // Export functions
    $('#export-excel').on('click', function(e) {
        e.preventDefault();
        exportToExcel();
    });
    
    $('#export-pdf').on('click', function(e) {
        e.preventDefault();
        exportToPDF();
    });
    
    // Functions
    function loadInscriptions() {
        showLoading();
        
        const data = {
            action: 'linmania_get_inscriptions',
            nonce: linmania_ajax.nonce,
            page: currentPage,
            per_page: perPage,
            search: currentFilters.search,
            categoria: currentFilters.categoria,
            local: currentFilters.local,
            sort_by: currentSort,
            sort_order: currentSortOrder
        };
        
        
        $.ajax({
            url: linmania_ajax.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    renderTable(response.data.inscriptions);
                    updatePagination(response.data);
                } else {
                    showError('Error al cargar las inscripciones: ' + response.data);
                }
                hideLoading();
            },
            error: function() {
                showError('Error de conexión al cargar las inscripciones');
                hideLoading();
            }
        });
    }
    
    function loadFilterOptions() {
        $.ajax({
            url: linmania_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'linmania_get_filter_options',
                nonce: linmania_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    populateFilterOptions(response.data);
                }
            }
        });
    }
    
    function populateFilterOptions(data) {
        // Populate local filter
        const localSelect = $('#local-filter');
        localSelect.empty().append('<option value="">Todos los locales</option>');
        if (data.locales && data.locales.length > 0) {
            data.locales.forEach(function(local) {
                localSelect.append(`<option value="${local}">${local}</option>`);
            });
        }

        // Populate categoria filter
        const categoriaSelect = $('#categoria-filter');
        categoriaSelect.empty().append('<option value="">Todas las categorías</option>');
        if (data.categorias && data.categorias.length > 0) {
            data.categorias.forEach(function(categoria) {
                categoriaSelect.append(`<option value="${categoria}">${categoria}</option>`);
            });
        }
    }
    
    function renderTable(inscriptions) {
        const tbody = $('#inscriptions-table tbody');
        tbody.empty();
        
        if (inscriptions.length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center">No se encontraron inscripciones</td></tr>');
            return;
        }
        
        inscriptions.forEach(function(inscription) {
            const row = $(`
                <tr>
                    <td>
                        <button class="expand-toggle" data-order-id="${inscription.ID}">+</button>
                    </td>
                    <td>${inscription.ID}</td>
                    <td>${inscription.date}</td>
                    <td>${inscription.categoria}</td>
                    <td>${inscription.local}</td>
                    <td>${inscription.equipo}</td>
                </tr>
            `);
            tbody.append(row);
        });
    }
    
    function updatePagination(data) {
        const paginationInfo = $('#pagination-info');
        const startRecord = (data.current_page - 1) * perPage + 1;
        const endRecord = Math.min(data.current_page * perPage, data.total);
        
        paginationInfo.text(`Mostrando registros del ${startRecord} al ${endRecord} de un total de ${data.total} registros`);
        
        // Update pagination buttons
        const pagination = $('#pagination');
        pagination.empty();
        
        if (data.pages > 1) {
            // Previous button
            if (data.current_page > 1) {
                pagination.append(`<a href="#" class="button" data-page="${data.current_page - 1}">« Anterior</a>`);
            }
            
            // Page numbers
            for (let i = 1; i <= data.pages; i++) {
                const activeClass = i === data.current_page ? 'button-primary' : 'button';
                pagination.append(`<a href="#" class="${activeClass}" data-page="${i}">${i}</a>`);
            }
            
            // Next button
            if (data.current_page < data.pages) {
                pagination.append(`<a href="#" class="button" data-page="${data.current_page + 1}">Siguiente »</a>`);
            }
        }
    }
    
    function showPlayerDetailsModal(orderId, inscription) {
        const modal = $(`
            <div class="linmania-modal" id="player-modal-${orderId}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Detalles del Equipo - Orden #${orderId}</h3>
                        <span class="close-modal">&times;</span>
                    </div>
                    <div class="modal-body">
                        <div class="players-grid-modal">
                            <div class="player-field-modal">
                                <label>Jugador 1:</label>
                                <span>${inscription.jugador1 || 'No especificado'}</span>
                            </div>
                            <div class="player-field-modal">
                                <label>Teléfono 1:</label>
                                <span>${inscription.telefono1 || 'No especificado'}</span>
                            </div>
                            <div class="player-field-modal">
                                <label>Jugador 2:</label>
                                <span>${inscription.jugador2 || 'No especificado'}</span>
                            </div>
                            <div class="player-field-modal">
                                <label>Teléfono 2:</label>
                                <span>${inscription.telefono2 || 'No especificado'}</span>
                            </div>
                            <div class="player-field-modal">
                                <label>Jugador 3:</label>
                                <span>${inscription.jugador3 || 'No especificado'}</span>
                            </div>
                            <div class="player-field-modal">
                                <label>Teléfono 3:</label>
                                <span>${inscription.telefono3 || 'No especificado'}</span>
                            </div>
                            <div class="player-field-modal">
                                <label>Jugador 4:</label>
                                <span>${inscription.jugador4 || 'No especificado'}</span>
                            </div>
                            <div class="player-field-modal">
                                <label>Teléfono 4:</label>
                                <span>${inscription.telefono4 || 'No especificado'}</span>
                            </div>
                        </div>
                        <div class="suplentes-field-modal">
                            <label>Suplentes:</label>
                            <span>${inscription.suplentes || 'No especificado'}</span>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(modal);
        modal.show();
        
        // Close modal events
        modal.find('.close-modal').on('click', function() {
            modal.remove();
        });
        
        modal.on('click', function(e) {
            if (e.target === modal[0]) {
                modal.remove();
            }
        });
    }
    
    // Handle expand/collapse for player details
    $(document).on('click', '.expand-toggle', function() {
        const orderId = $(this).data('order-id');
        const button = $(this);
        
        // Get inscription data from the current page data
        $.ajax({
            url: linmania_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'linmania_get_player_details',
                nonce: linmania_ajax.nonce,
                order_id: orderId
            },
            success: function(response) {
                if (response.success) {
                    showPlayerDetailsModal(orderId, response.data);
                } else {
                    alert('Error al cargar los detalles del equipo');
                }
            },
            error: function() {
                alert('Error de conexión al cargar los detalles del equipo');
            }
        });
    });
    
    function exportToExcel() {
        window.location.href = linmania_ajax.ajax_url + '?action=linmania_export_excel&nonce=' + linmania_ajax.nonce;
    }
    
    function exportToPDF() {
        window.location.href = linmania_ajax.ajax_url + '?action=linmania_export_pdf&nonce=' + linmania_ajax.nonce;
    }
    
    function showLoading() {
        $('#inscriptions-table tbody').html('<tr><td colspan="6" class="text-center">Cargando...</td></tr>');
    }
    
    function hideLoading() {
        // Loading is hidden when table is rendered
    }
    
    function showError(message) {
        $('#inscriptions-table tbody').html(`<tr><td colspan="6" class="text-center error">${message}</td></tr>`);
    }
});
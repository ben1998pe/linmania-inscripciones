jQuery(document).ready(function($) {
    'use strict';
    
    let currentPage = 1;
    let perPage = 25;
    let currentSort = 'ID';
    let currentSortOrder = 'DESC';
    let currentFilters = {
        search: '',
        categoria: '',
        local: '',
        equipo: ''
    };
    
    // Initialize
    loadInscriptions();
    loadFilterOptions();
    
    // Event listeners
    $('#records-per-page').on('change', function() {
        perPage = parseInt($(this).val());
        currentPage = 1;
        loadInscriptions();
    });
    
    $('#general-search').on('keyup', debounce(function() {
        currentFilters.search = $(this).val();
        currentPage = 1;
        loadInscriptions();
    }, 500));
    
    $('#categoria-filter, #local-filter, #equipo-filter').on('change keyup', function() {
        const filterType = $(this).attr('id').replace('-filter', '');
        currentFilters[filterType] = $(this).val();
        currentPage = 1;
        loadInscriptions();
    });
    
    $('.sortable').on('click', function() {
        const sortField = $(this).data('sort');
        
        if (currentSort === sortField) {
            currentSortOrder = currentSortOrder === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentSort = sortField;
            currentSortOrder = 'DESC';
        }
        
        // Update visual indicators
        $('.sortable').removeClass('sort-asc sort-desc');
        $(this).addClass(currentSortOrder === 'ASC' ? 'sort-asc' : 'sort-desc');
        
        currentPage = 1;
        loadInscriptions();
    });
    
    // Expand/collapse functionality
    $(document).on('click', '.expand-toggle', function() {
        const row = $(this).closest('tr');
        const detailsRow = row.next('.player-details');
        
        if (detailsRow.length) {
            detailsRow.toggleClass('show');
            $(this).text(detailsRow.hasClass('show') ? '−' : '+');
            row.toggleClass('expanded');
        } else {
            // Load player details via AJAX
            const orderId = row.data('order-id');
            loadPlayerDetails(orderId, row);
        }
    });
    
    // Export functionality
    $('#export-excel').on('click', function(e) {
        e.preventDefault();
        exportData('excel');
    });
    
    $('#export-pdf').on('click', function(e) {
        e.preventDefault();
        exportData('pdf');
    });
    
    // Test data functionality removed - no longer needed
    
    $('#debug-database').on('click', function(e) {
        e.preventDefault();
        debugDatabase();
    });
    
    $('#test-orders').on('click', function(e) {
        e.preventDefault();
        testOrdersQuery();
    });
    
    // Modal functionality
    $('.close').on('click', function() {
        $('#player-details-modal').hide();
    });
    
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('linmania-modal')) {
            $('#player-details-modal').hide();
        }
    });
    
    // Functions
    function loadInscriptions() {
        showLoading();
        
        $.ajax({
            url: linmania_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'linmania_get_inscriptions',
                nonce: linmania_ajax.nonce,
                page: currentPage,
                per_page: perPage,
                search: currentFilters.search,
                categoria: currentFilters.categoria,
                local: currentFilters.local,
                equipo: currentFilters.equipo,
                sort_by: currentSort,
                sort_order: currentSortOrder
            },
            success: function(response) {
                console.log('Response received:', response);
                
                if (response.success) {
                    renderTable(response.data.inscriptions);
                    renderPagination(response.data);
                    
                    // Mostrar información de debug en consola
                    if (response.data.debug) {
                        console.log('Debug info:', response.data.debug);
                    }
                } else {
                    console.error('Error response:', response);
                    showError('Error al cargar las inscripciones: ' + (response.data || 'Error desconocido'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                showError('Error de conexión: ' + error);
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    function loadFilterOptions() {
        // Load unique values for filters
        $.ajax({
            url: linmania_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'linmania_get_filter_options',
                nonce: linmania_ajax.nonce
            },
            success: function(response) {
                console.log('Filter options response:', response);
                if (response.success) {
                    populateFilterOptions(response.data);
                } else {
                    console.error('Error loading filter options:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading filter options:', error);
            }
        });
    }
    
    function populateFilterOptions(data) {
        console.log('Filter options data:', data);
        
        // Populate local filter
        const localSelect = $('#local-filter');
        localSelect.empty().append('<option value="">Nothing selected</option>');
        if (data.locales && data.locales.length > 0) {
            data.locales.forEach(function(local) {
                localSelect.append(`<option value="${local}">${local}</option>`);
            });
        }
        
        // Populate categoria filter
        const categoriaSelect = $('#categoria-filter');
        categoriaSelect.empty().append('<option value="">Nothing selected</option>');
        if (data.categorias && data.categorias.length > 0) {
            data.categorias.forEach(function(categoria) {
                categoriaSelect.append(`<option value="${categoria}">${categoria}</option>`);
            });
        }
    }
    
    function renderTable(inscriptions) {
        const tbody = $('#inscriptions-tbody');
        tbody.empty();
        
        if (inscriptions.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="5" class="linmania-loading">
                        No se encontraron inscripciones
                    </td>
                </tr>
            `);
            return;
        }
        
        inscriptions.forEach(function(inscription) {
            const row = $(`
                <tr data-order-id="${inscription.ID}">
                    <td>
                        <button class="expand-toggle">+</button>
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
    
    function loadPlayerDetails(orderId, row) {
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
                    showPlayerDetailsModal(response.data, orderId);
                } else {
                    console.error('Error al cargar detalles:', response.data);
                    alert('Error al cargar los detalles de los jugadores');
                }
            }
        });
    }
    
    function showPlayerDetailsModal(playerData, orderId) {
        // Crear el modal
        const modal = $(`
            <div class="linmania-modal" id="player-details-modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Detalles de Jugadores - Orden #${orderId}</h2>
                    <div class="players-grid-modal">
                        <div class="player-field-modal">
                            <label>Jugador 1:</label>
                            <span>${playerData.jugador1 || 'No especificado'}</span>
                            ${playerData.telefono1 ? `<br><small>Tel: ${playerData.telefono1}</small>` : ''}
                        </div>
                        <div class="player-field-modal">
                            <label>Jugador 2:</label>
                            <span>${playerData.jugador2 || 'No especificado'}</span>
                            ${playerData.telefono2 ? `<br><small>Tel: ${playerData.telefono2}</small>` : ''}
                        </div>
                        <div class="player-field-modal">
                            <label>Jugador 3:</label>
                            <span>${playerData.jugador3 || 'No especificado'}</span>
                            ${playerData.telefono3 ? `<br><small>Tel: ${playerData.telefono3}</small>` : ''}
                        </div>
                        <div class="player-field-modal">
                            <label>Jugador 4:</label>
                            <span>${playerData.jugador4 || 'No especificado'}</span>
                            ${playerData.telefono4 ? `<br><small>Tel: ${playerData.telefono4}</small>` : ''}
                        </div>
                    </div>
                    <div class="suplentes-field-modal">
                        <label>Suplentes:</label>
                        <span>${playerData.suplentes || 'No especificados'}</span>
                    </div>
                </div>
            </div>
        `);
        
        // Agregar el modal al body
        $('body').append(modal);
        
        // Mostrar el modal
        modal.fadeIn(300);
        
        // Eventos para cerrar el modal
        $('.close, .linmania-modal').on('click', function(e) {
            if (e.target === this) {
                modal.fadeOut(300, function() {
                    modal.remove();
                });
            }
        });
        
        // Cerrar con tecla ESC
        $(document).on('keydown.modal', function(e) {
            if (e.keyCode === 27) { // ESC
                modal.fadeOut(300, function() {
                    modal.remove();
                });
                $(document).off('keydown.modal');
            }
        });
    }
    
    function renderPagination(data) {
        const total = data.total;
        const pages = data.pages;
        const current = data.current_page;
        
        // Update pagination info
        const start = (current - 1) * perPage + 1;
        const end = Math.min(current * perPage, total);
        $('#pagination-info').text(`Mostrando registros del ${start} al ${end} de un total de ${total} registros`);
        
        // Render pagination links
        const paginationLinks = $('#pagination-links');
        paginationLinks.empty();
        
        if (pages <= 1) return;
        
        // Previous button
        if (current > 1) {
            paginationLinks.append(`<a href="#" class="prev-page" data-page="${current - 1}">« Anterior</a>`);
        } else {
            paginationLinks.append('<span class="disabled">« Anterior</span>');
        }
        
        // Page numbers
        const startPage = Math.max(1, current - 2);
        const endPage = Math.min(pages, current + 2);
        
        if (startPage > 1) {
            paginationLinks.append('<a href="#" class="page-link" data-page="1">1</a>');
            if (startPage > 2) {
                paginationLinks.append('<span class="disabled">...</span>');
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === current) {
                paginationLinks.append(`<span class="current">${i}</span>`);
            } else {
                paginationLinks.append(`<a href="#" class="page-link" data-page="${i}">${i}</a>`);
            }
        }
        
        if (endPage < pages) {
            if (endPage < pages - 1) {
                paginationLinks.append('<span class="disabled">...</span>');
            }
            paginationLinks.append(`<a href="#" class="page-link" data-page="${pages}">${pages}</a>`);
        }
        
        // Next button
        if (current < pages) {
            paginationLinks.append(`<a href="#" class="next-page" data-page="${current + 1}">Siguiente »</a>`);
        } else {
            paginationLinks.append('<span class="disabled">Siguiente »</span>');
        }
        
        // Add click handlers
        paginationLinks.find('.page-link, .prev-page, .next-page').on('click', function(e) {
            e.preventDefault();
            currentPage = parseInt($(this).data('page'));
            loadInscriptions();
        });
    }
    
    function exportData(format) {
        const params = new URLSearchParams({
            action: `linmania_export_${format}`,
            nonce: linmania_ajax.nonce,
            search: currentFilters.search,
            categoria: currentFilters.categoria,
            local: currentFilters.local,
            equipo: currentFilters.equipo,
            sort_by: currentSort,
            sort_order: currentSortOrder
        });
        
        window.open(`${linmania_ajax.ajax_url}?${params.toString()}`, '_blank');
    }
    
    function showLoading() {
        $('#inscriptions-tbody').html(`
            <tr>
                <td colspan="6" class="linmania-loading">
                    Cargando inscripciones...
                </td>
            </tr>
        `);
    }
    
    function hideLoading() {
        // Loading is replaced by actual data
    }
    
    function showError(message) {
        $('#inscriptions-tbody').html(`
            <tr>
                <td colspan="6" style="text-align: center; color: #d63638; padding: 20px;">
                    ${message}
                </td>
            </tr>
        `);
    }
    
    // Test data functions removed - no longer needed
    
    function debugDatabase() {
        const button = $('#debug-database');
        const status = $('#test-data-status');
        
        button.prop('disabled', true).text('Analizando...');
        status.text('Analizando base de datos...');
        
        $.ajax({
            url: linmania_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'linmania_debug_database',
                nonce: linmania_ajax.nonce
            },
            success: function(response) {
                console.log('Database debug response:', response);
                
                if (response.success) {
                    const data = response.data;
                    let message = `WordPress: ${data.wp_info.wp_version} | PHP: ${data.wp_info.php_version} | MySQL: ${data.wp_info.mysql_version} | `;
                    message += `WooCommerce: ${data.wc_info.wc_active ? data.wc_info.wc_version : 'No activo'} | `;
                    message += `Órdenes encontradas: ${data.total_shop_orders}`;
                    
                    status.text(message).css('color', 'blue');
                    
                    // Mostrar información detallada en consola
                    console.log('=== DEBUG DE BASE DE DATOS ===');
                    console.log('Información WordPress:', data.wp_info);
                    console.log('Información WooCommerce:', data.wc_info);
                    console.log('Tablas:', data.tables);
                    console.log('Tipos de posts:', data.post_types);
                    console.log('Órdenes de WooCommerce:', data.shop_orders);
                    console.log('Estados de órdenes:', data.order_statuses);
                    console.log('Meta campos de órdenes:', data.order_meta);
                } else {
                    status.text('Error en debug: ' + (response.data || 'Error desconocido')).css('color', 'red');
                }
            },
            error: function(xhr, status, error) {
                console.error('Debug error:', error);
                status.text('Error de conexión: ' + error).css('color', 'red');
            },
            complete: function() {
                button.prop('disabled', false).text('Debug Base de Datos');
            }
        });
    }
    
    function testOrdersQuery() {
        const button = $('#test-orders');
        const status = $('#test-data-status');
        
        button.prop('disabled', true).text('Probando...');
        status.text('Probando diferentes consultas de órdenes...');
        
        $.ajax({
            url: linmania_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'linmania_test_orders',
                nonce: linmania_ajax.nonce
            },
            success: function(response) {
                console.log('Test orders response:', response);
                
                if (response.success) {
                    const data = response.data;
                    let message = 'Pruebas de consulta completadas - Ver consola para detalles';
                    
                    status.text(message).css('color', 'blue');
                    
                    // Mostrar información detallada en consola
                    console.log('=== PRUEBAS DE CONSULTA DE ÓRDENES ===');
                    console.log('Resultados de consultas:', data.test_queries);
                    
                    // Buscar la consulta que funciona
                    let workingQuery = null;
                    for (const [queryName, result] of Object.entries(data.test_queries)) {
                        if (result.found_posts > 0 || result.count > 0) {
                            workingQuery = queryName;
                            console.log(`✅ Consulta que funciona: ${queryName}`, result);
                            break;
                        }
                    }
                    
                    if (workingQuery) {
                        console.log(`🎯 Usar esta consulta: ${workingQuery}`);
                        status.text(`Consulta que funciona encontrada: ${workingQuery}`).css('color', 'green');
                    } else {
                        console.log('❌ Ninguna consulta encontró órdenes');
                        status.text('Ninguna consulta encontró órdenes').css('color', 'red');
                    }
                } else {
                    status.text('Error en prueba: ' + (response.data || 'Error desconocido')).css('color', 'red');
                }
            },
            error: function(xhr, status, error) {
                console.error('Test orders error:', error);
                status.text('Error de conexión: ' + error).css('color', 'red');
            },
            complete: function() {
                button.prop('disabled', false).text('Probar Consulta de Órdenes');
            }
        });
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});

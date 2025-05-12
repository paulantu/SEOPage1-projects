jQuery(document).ready(function($) {
    
    // Variables to store selected taxonomies
    let selectedCategories = [];
    let selectedTypes = [];
    let selectedLocations = [];
    let selectedAmenities = [];
    let selectedAreas = [];
    let selectedAttractions = [];
    let selectedCommunities = [];
    let selectedTags = [];
    
    // Variables to track repeater indexes
    let overviewIndex = 0;
    let propertyDetailsIndex = 0;
    let amenitiesIndex = 0;
    
    // Initialize repeaters
    initRepeaters();
    
    // Function to initialize repeaters
    function initRepeaters() {
        // Add initial items if none exist
        if ($('#overview_repeater .repeater-items').children().length === 0) {
            addRepeaterItem('overview');
        }
        
        if ($('#property_details_repeater .repeater-items').children().length === 0) {
            addRepeaterItem('property_details');
        }
        
        if ($('#amenities_repeater .repeater-items').children().length === 0) {
            addRepeaterItem('amenities');
        }
    }
    
    // Function to add repeater item
    function addRepeaterItem(repeaterType) {
        let template, container, index;
        
        switch (repeaterType) {
            case 'overview':
                template = $('#overview_repeater .repeater-template').html();
                container = $('#overview_repeater .repeater-items');
                index = overviewIndex++;
                break;
            case 'property_details':
                template = $('#property_details_repeater .repeater-template').html();
                container = $('#property_details_repeater .repeater-items');
                index = propertyDetailsIndex++;
                break;
            case 'amenities':
                template = $('#amenities_repeater .repeater-template').html();
                container = $('#amenities_repeater .repeater-items');
                index = amenitiesIndex++;
                break;
            default:
                return;
        }
        
        // Replace index placeholder
        template = template.replace(/__index__/g, index);
        
        // Add the item to container
        container.append(template);
        
        // Initialize media uploaders for the new item
        if (repeaterType === 'overview' || repeaterType === 'amenities') {
            initMediaUploaders();
        }
    }
    
    // Add repeater item button handler
    $('.add-repeater-item').on('click', function() {
        const repeaterType = $(this).data('repeater');
        addRepeaterItem(repeaterType);
    });
    
    // Remove repeater item handler
    $(document).on('click', '.remove-repeater-item', function() {
        $(this).closest('.repeater-item').remove();
    });
    
    // Initialize all media uploaders
    initMediaUploaders();
    initGalleryUploader();
    
    // Function to get taxonomy suggestions
    function getTaxonomySuggestions(term, taxonomy, callback) {
        $.ajax({
            url: property_submission.ajax_url,
            type: 'POST',
            data: {
                action: 'get_property_taxonomy_suggestions',
                nonce: property_submission.nonce,
                term: term,
                taxonomy: taxonomy
            },
            success: function(response) {
                if (response.success) {
                    callback(response.data.suggestions);
                } else {
                    console.error(response.data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ' + status + ' - ' + error);
            }
        });
    }
    
    // Function to render taxonomy suggestions
    function renderTaxonomySuggestions(suggestions, container, isSelected, taxonomy) {
        container.empty();
        
        if (suggestions.length === 0) {
            // Add a suggestion to create a new term
            const term = $('#' + taxonomy + '_input').val();
            if (term && term.length > 0) {
                container.append(
                    $('<div>')
                        .addClass('taxonomy-suggestion-item')
                        .text('Create: "' + term + '"')
                        .data('suggestion', { name: term })
                );
            } else {
                container.hide();
                return;
            }
        }
        
        // Add each suggestion
        $.each(suggestions, function(index, suggestion) {
            // Skip if already selected
            if (isSelected(suggestion.id)) {
                return;
            }
            
            container.append(
                $('<div>')
                    .addClass('taxonomy-suggestion-item')
                    .text(suggestion.name)
                    .data('suggestion', suggestion)
            );
        });
        
        container.show();
    }
    
    // Function to add a selected taxonomy
    function addSelectedTaxonomy(taxonomy, container, selectedArray, hiddenInput) {
        const selectedItem = $('<div>')
            .addClass('selected-taxonomy')
            .text(taxonomy.name)
            .append(
                $('<span>')
                    .addClass('remove-taxonomy')
                    .html('&times;')
                    .data('id', taxonomy.id)
                    .data('name', taxonomy.name)
            );
        
        container.append(selectedItem);
        selectedArray.push(taxonomy);
        updateHiddenInput(selectedArray, hiddenInput);
    }
    
    // Function to update hidden input with JSON of selected taxonomies
    function updateHiddenInput(selectedArray, hiddenInput) {
        $('#' + hiddenInput).val(JSON.stringify(selectedArray));
    }
    
    // Category input handler
    $('#category_input').on('keyup', function() {
        const term = $(this).val();
        
        if (term.length < 2) {
            $('#category_suggestions').hide();
            return;
        }
        
        const isAlreadySelected = function(id) {
            return selectedCategories.some(function(category) {
                return category.id === id;
            });
        };
        
        getTaxonomySuggestions(term, 'category', function(suggestions) {
            renderTaxonomySuggestions(suggestions, $('#category_suggestions'), isAlreadySelected, 'category');
        });
    });
    
    // Category suggestion click handler
    $(document).on('click', '#category_suggestions .taxonomy-suggestion-item', function() {
        const suggestion = $(this).data('suggestion');
        addSelectedTaxonomy(suggestion, $('#selected_categories'), selectedCategories, 'property_categories');
        $('#category_input').val('').focus();
        $('#category_suggestions').hide();
    });
    
    // Property Type input handler
    $('#type_input').on('keyup', function() {
        const term = $(this).val();
        
        if (term.length < 2) {
            $('#type_suggestions').hide();
            return;
        }
        
        const isAlreadySelected = function(id) {
            return selectedTypes.some(function(type) {
                return type.id === id;
            });
        };
        
        getTaxonomySuggestions(term, 'type', function(suggestions) {
            renderTaxonomySuggestions(suggestions, $('#type_suggestions'), isAlreadySelected, 'type');
        });
    });
    
    // Property Type suggestion click handler
    $(document).on('click', '#type_suggestions .taxonomy-suggestion-item', function() {
        const suggestion = $(this).data('suggestion');
        addSelectedTaxonomy(suggestion, $('#selected_types'), selectedTypes, 'property_types');
        $('#type_input').val('').focus();
        $('#type_suggestions').hide();
    });
    
    // Location input handler
    $('#location_input').on('keyup', function() {
        const term = $(this).val();
        
        if (term.length < 2) {
            $('#location_suggestions').hide();
            return;
        }
        
        const isAlreadySelected = function(id) {
            return selectedLocations.some(function(location) {
                return location.id === id;
            });
        };
        
        getTaxonomySuggestions(term, 'location', function(suggestions) {
            renderTaxonomySuggestions(suggestions, $('#location_suggestions'), isAlreadySelected, 'location');
        });
    });
    
    // Location suggestion click handler
    $(document).on('click', '#location_suggestions .taxonomy-suggestion-item', function() {
        const suggestion = $(this).data('suggestion');
        addSelectedTaxonomy(suggestion, $('#selected_locations'), selectedLocations, 'property_locations');
        $('#location_input').val('').focus();
        $('#location_suggestions').hide();
    });
    
    // Amenities input handler
    $('#amenities_input').on('keyup', function() {
        const term = $(this).val();
        
        if (term.length < 2) {
            $('#amenities_suggestions').hide();
            return;
        }
        
        const isAlreadySelected = function(id) {
            return selectedAmenities.some(function(amenity) {
                return amenity.id === id;
            });
        };
        
        getTaxonomySuggestions(term, 'amenities', function(suggestions) {
            renderTaxonomySuggestions(suggestions, $('#amenities_suggestions'), isAlreadySelected, 'amenities');
        });
    });
    
    // Amenities suggestion click handler
    $(document).on('click', '#amenities_suggestions .taxonomy-suggestion-item', function() {
        const suggestion = $(this).data('suggestion');
        addSelectedTaxonomy(suggestion, $('#selected_amenities'), selectedAmenities, 'property_amenities');
        $('#amenities_input').val('').focus();
        $('#amenities_suggestions').hide();
    });
    
    // Area input handler
    $('#area_input').on('keyup', function() {
        const term = $(this).val();
        
        if (term.length < 2) {
            $('#area_suggestions').hide();
            return;
        }
        
        const isAlreadySelected = function(id) {
            return selectedAreas.some(function(area) {
                return area.id === id;
            });
        };
        
        getTaxonomySuggestions(term, 'area', function(suggestions) {
            renderTaxonomySuggestions(suggestions, $('#area_suggestions'), isAlreadySelected, 'area');
        });
    });
    
    // Area suggestion click handler
    $(document).on('click', '#area_suggestions .taxonomy-suggestion-item', function() {
        const suggestion = $(this).data('suggestion');
        addSelectedTaxonomy(suggestion, $('#selected_areas'), selectedAreas, 'property_areas');
        $('#area_input').val('').focus();
        $('#area_suggestions').hide();
    });
    
    // Attraction input handler
    $('#attraction_input').on('keyup', function() {
        const term = $(this).val();
        
        if (term.length < 2) {
            $('#attraction_suggestions').hide();
            return;
        }
        
        const isAlreadySelected = function(id) {
            return selectedAttractions.some(function(attraction) {
                return attraction.id === id;
            });
        };
        
        getTaxonomySuggestions(term, 'attraction', function(suggestions) {
            renderTaxonomySuggestions(suggestions, $('#attraction_suggestions'), isAlreadySelected, 'attraction');
        });
    });
    
    // Attraction suggestion click handler
    $(document).on('click', '#attraction_suggestions .taxonomy-suggestion-item', function() {
        const suggestion = $(this).data('suggestion');
        addSelectedTaxonomy(suggestion, $('#selected_attractions'), selectedAttractions, 'property_attractions');
        $('#attraction_input').val('').focus();
        $('#attraction_suggestions').hide();
    });
    
    // Community input handler
    $('#community_input').on('keyup', function() {
        const term = $(this).val();
        
        if (term.length < 2) {
            $('#community_suggestions').hide();
            return;
        }
        
        const isAlreadySelected = function(id) {
            return selectedCommunities.some(function(community) {
                return community.id === id;
            });
        };
        
        getTaxonomySuggestions(term, 'community', function(suggestions) {
            renderTaxonomySuggestions(suggestions, $('#community_suggestions'), isAlreadySelected, 'community');
        });
    });
    
    // Community suggestion click handler
    $(document).on('click', '#community_suggestions .taxonomy-suggestion-item', function() {
        const suggestion = $(this).data('suggestion');
        addSelectedTaxonomy(suggestion, $('#selected_communities'), selectedCommunities, 'property_communities');
        $('#community_input').val('').focus();
        $('#community_suggestions').hide();
    });
    
    // Tag input handler
    $('#tag_input').on('keyup', function() {
        const term = $(this).val();
        
        if (term.length < 2) {
            $('#tag_suggestions').hide();
            return;
        }
        
        const isAlreadySelected = function(id) {
            return selectedTags.some(function(tag) {
                return tag.id === id;
            });
        };
        
        getTaxonomySuggestions(term, 'tag', function(suggestions) {
            renderTaxonomySuggestions(suggestions, $('#tag_suggestions'), isAlreadySelected, 'tag');
        });
    });
    
    // Tag suggestion click handler
    $(document).on('click', '#tag_suggestions .taxonomy-suggestion-item', function() {
        const suggestion = $(this).data('suggestion');
        addSelectedTaxonomy(suggestion, $('#selected_tags'), selectedTags, 'property_tags');
        $('#tag_input').val('').focus();
        $('#tag_suggestions').hide();
    });
    
    // Remove selected taxonomy handler
    $(document).on('click', '.remove-taxonomy', function() {
        const taxonomyElement = $(this).parent();
        const id = $(this).data('id');
        const name = $(this).data('name');
        const parentId = taxonomyElement.parent().attr('id');
        
        switch (parentId) {
            case 'selected_categories':
                selectedCategories = selectedCategories.filter(function(category) {
                    return !(category.id === id && category.name === name);
                });
                updateHiddenInput(selectedCategories, 'property_categories');
                break;
            case 'selected_types':
                selectedTypes = selectedTypes.filter(function(type) {
                    return !(type.id === id && type.name === name);
                });
                updateHiddenInput(selectedTypes, 'property_types');
                break;
            case 'selected_locations':
                selectedLocations = selectedLocations.filter(function(location) {
                    return !(location.id === id && location.name === name);
                });
                updateHiddenInput(selectedLocations, 'property_locations');
                break;
            case 'selected_amenities':
                selectedAmenities = selectedAmenities.filter(function(amenity) {
                    return !(amenity.id === id && amenity.name === name);
                });
                updateHiddenInput(selectedAmenities, 'property_amenities');
                break;
            case 'selected_areas':
                selectedAreas = selectedAreas.filter(function(area) {
                    return !(area.id === id && area.name === name);
                });
                updateHiddenInput(selectedAreas, 'property_areas');
                break;
            case 'selected_attractions':
                selectedAttractions = selectedAttractions.filter(function(attraction) {
                    return !(attraction.id === id && attraction.name === name);
                });
                updateHiddenInput(selectedAttractions, 'property_attractions');
                break;
            case 'selected_communities':
                selectedCommunities = selectedCommunities.filter(function(community) {
                    return !(community.id === id && community.name === name);
                });
                updateHiddenInput(selectedCommunities, 'property_communities');
                break;
            case 'selected_tags':
                selectedTags = selectedTags.filter(function(tag) {
                    return !(tag.id === id && tag.name === name);
                });
                updateHiddenInput(selectedTags, 'property_tags');
                break;
        }
        
        taxonomyElement.remove();
    });
    
    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.taxonomy-input-wrapper').length) {
            $('.taxonomy-suggestions').hide();
        }
    });
    
    // Function to initialize media uploaders
    function initMediaUploaders() {
        $('.select-media-btn').each(function() {
            if ($(this).data('initialized')) {
                return;
            }
            
            $(this).data('initialized', true);
            
            $(this).on('click', function(e) {
                e.preventDefault();
                
                const button = $(this);
                const previewContainer = button.closest('.upload-preview');
                let inputField;
                
                // Determine which input field to update based on the container
                if (previewContainer.attr('id') === 'featured-image-preview') {
                    inputField = $('#featured_image_id');
                } else if (previewContainer.attr('id') === 'floor-plan-image-preview') {
                    inputField = $('#floor_plan_image');
                } else if (previewContainer.attr('id') === 'video-preview') {
                    inputField = $('#video');
                } else if (previewContainer.attr('id') === 'brochure-preview') {
                    inputField = $('#property_brochure');
                } else if (previewContainer.hasClass('overview-icon-preview')) {
                    inputField = previewContainer.closest('.form-group').find('.overview-icon-id');
                } else if (previewContainer.hasClass('amenities-image-preview')) {
                    inputField = previewContainer.closest('.form-group').find('.amenities-image-id');
                } else {
                    return;
                }
                
                let mediaType = 'image';
                
                if (previewContainer.attr('id') === 'video-preview') {
                    mediaType = 'video';
                } else if (previewContainer.attr('id') === 'brochure-preview') {
                    mediaType = 'application/pdf';
                }
                
                // Create and open media uploader
                const mediaUploader = wp.media({
                    title: property_submission.media_title,
                    button: {
                        text: property_submission.media_button
                    },
                    library: {
                        type: mediaType
                    },
                    multiple: false
                });
                
                mediaUploader.on('select', function() {
                    const attachment = mediaUploader.state().get('selection').first().toJSON();
                    inputField.val(attachment.id);
                    
                    if (mediaType === 'image') {
                        if (attachment.sizes && attachment.sizes.thumbnail) {
                            previewContainer.css('background-image', 'url(' + attachment.sizes.thumbnail.url + ')');
                        } else {
                            previewContainer.css('background-image', 'url(' + attachment.url + ')');
                        }
                        previewContainer.addClass('has-file');
                    } else {
                        // For non-image files, show file info
                        const fileInfo = $('<div class="file-info"></div>');
                        const fileIcon = $('<span class="file-info-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg></span>');
                        const fileName = $('<span class="file-info-name"></span>').text(attachment.filename);
                        const removeButton = $('<span class="remove-file">&times;</span>');
                        
                        fileInfo.append(fileIcon, fileName, removeButton);
                        
                        // Remove any existing file info
                        previewContainer.find('.file-info').remove();
                        previewContainer.append(fileInfo);
                        previewContainer.addClass('has-file');
                        
                        // Handle remove button
                        removeButton.on('click', function(e) {
                            e.stopPropagation();
                            inputField.val('');
                            previewContainer.removeClass('has-file');
                            fileInfo.remove();
                        });
                    }
                });
                
                mediaUploader.open();
            });
        });
        
        // Drag and drop functionality for image uploads
        $('.upload-preview').each(function() {
            if ($(this).data('drag-initialized')) {
                return;
            }
            
            $(this).data('drag-initialized', true);
            
            const dropArea = $(this);
            const inputField = getInputFieldForDropArea(dropArea);
            
            if (!inputField) {
                return;
            }
            
            // Prevent default behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea[0].addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            // Highlight drop area when file is dragged over it
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea[0].addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropArea[0].addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                dropArea.addClass('drag-highlight');
            }
            
            function unhighlight() {
                dropArea.removeClass('drag-highlight');
            }
            
            // Handle the dropped files
            dropArea[0].addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    uploadFile(files[0]);
                }
            }
            
            function uploadFile(file) {
                // Check if it's a gallery drop area
                if (dropArea.attr('id') === 'gallery-preview') {
                    return; // This will be handled by gallery uploader
                }
                
                // Check file type based on drop area
                let validType = true;
                
                if (dropArea.attr('id') === 'video-preview' && !file.type.startsWith('video/')) {
                    validType = false;
                } else if (dropArea.attr('id') === 'brochure-preview' && file.type !== 'application/pdf') {
                    validType = false;
                } else if ((dropArea.attr('id') === 'featured-image-preview' || 
                           dropArea.hasClass('overview-icon-preview') || 
                           dropArea.hasClass('amenities-image-preview') || 
                           dropArea.attr('id') === 'floor-plan-image-preview') && 
                           !file.type.startsWith('image/')) {
                    validType = false;
                }
                
                if (!validType) {
                    alert('Please upload a file of the correct type.');
                    return;
                }
                
                // Create FormData
                const formData = new FormData();
                formData.append('file', file);
                formData.append('action', 'upload_property_media');
                formData.append('nonce', property_submission.nonce);
                
                // Upload using AJAX
                $.ajax({
                    url: property_submission.ajax_url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success) {
                            inputField.val(response.data.id);
                            
                            if (file.type.startsWith('image/')) {
                                dropArea.css('background-image', 'url(' + response.data.url + ')');
                                dropArea.addClass('has-file');
                            } else {
                                // For non-image files
                                const fileInfo = $('<div class="file-info"></div>');
                                const fileIcon = $('<span class="file-info-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg></span>');
                                const fileName = $('<span class="file-info-name"></span>').text(file.name);
                                const removeButton = $('<span class="remove-file">&times;</span>');
                                
                                fileInfo.append(fileIcon, fileName, removeButton);
                                
                                // Remove any existing file info
                                dropArea.find('.file-info').remove();
                                dropArea.append(fileInfo);
                                dropArea.addClass('has-file');
                                
                                // Handle remove button
                                removeButton.on('click', function(e) {
                                    e.stopPropagation();
                                    inputField.val('');
                                    dropArea.removeClass('has-file');
                                    fileInfo.remove();
                                });
                            }
                        } else {
                            alert('Upload failed: ' + response.data.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred during upload. Please try again.');
                    }
                });
            }
        });
    }
    
    // Helper function to get input field based on drop area
    function getInputFieldForDropArea(dropArea) {
        if (dropArea.attr('id') === 'featured-image-preview') {
            return $('#featured_image_id');
        } else if (dropArea.attr('id') === 'floor-plan-image-preview') {
            return $('#floor_plan_image');
        } else if (dropArea.attr('id') === 'video-preview') {
            return $('#video');
        } else if (dropArea.attr('id') === 'brochure-preview') {
            return $('#property_brochure');
        } else if (dropArea.hasClass('overview-icon-preview')) {
            return dropArea.closest('.form-group').find('.overview-icon-id');
        } else if (dropArea.hasClass('amenities-image-preview')) {
            return dropArea.closest('.form-group').find('.amenities-image-id');
        }
        return null;
    }
    
    // Gallery uploader initialization
    function initGalleryUploader() {
        const galleryPreview = $('#gallery-preview');
        const galleryContainer = $('.gallery-images-container');
        const galleryInput = $('#image_gallery');
        let galleryIds = [];
        
        // Select gallery images button
        $('.select-gallery-btn').on('click', function(e) {
            e.preventDefault();
            
            const galleryUploader = wp.media({
                title: 'Select Gallery Images',
                button: {
                    text: 'Add to Gallery'
                },
                library: {
                    type: 'image'
                },
                multiple: true
            });
            
            galleryUploader.on('select', function() {
                const attachments = galleryUploader.state().get('selection').toJSON();
                
                attachments.forEach(function(attachment) {
                    addGalleryImage(attachment);
                });
                
                updateGalleryInput();
            });
            
            galleryUploader.open();
        });
        
        // Function to add gallery image
        function addGalleryImage(attachment) {
            if (galleryIds.includes(attachment.id)) {
                return; // Skip duplicates
            }
            
            galleryIds.push(attachment.id);
            
            const imageItem = $('<div class="gallery-image-item"></div>');
            const image = $('<img>');
            
            if (attachment.sizes && attachment.sizes.thumbnail) {
                image.attr('src', attachment.sizes.thumbnail.url);
            } else {
                image.attr('src', attachment.url);
            }
            
            const removeButton = $('<div class="remove-gallery-image">&times;</div>');
            
            imageItem.append(image, removeButton);
            imageItem.data('id', attachment.id);
            
            galleryContainer.append(imageItem);
            
            // Handle remove button
            removeButton.on('click', function() {
                const id = imageItem.data('id');
                galleryIds = galleryIds.filter(function(galleryId) {
                    return galleryId != id;
                });
                
                imageItem.remove();
                updateGalleryInput();
            });
        }
        
        // Update gallery input field
        function updateGalleryInput() {
            galleryInput.val(galleryIds.join(','));
        }
        
        // Drag and drop for gallery
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            galleryPreview[0].addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            galleryPreview[0].addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            galleryPreview[0].addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            galleryPreview.addClass('drag-highlight');
        }
        
        function unhighlight() {
            galleryPreview.removeClass('drag-highlight');
        }
        
        galleryPreview[0].addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                Array.from(files).forEach(uploadGalleryFile);
            }
        }
        
        function uploadGalleryFile(file) {
            if (!file.type.startsWith('image/')) {
                alert('Please upload image files only for the gallery.');
                return;
            }
            
            const formData = new FormData();
            formData.append('file', file);
            formData.append('action', 'upload_property_media');
            formData.append('nonce', property_submission.nonce);
            
            $.ajax({
                url: property_submission.ajax_url,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        addGalleryImage({
                            id: response.data.id,
                            url: response.data.url,
                            sizes: {
                                thumbnail: {
                                    url: response.data.url
                                }
                            }
                        });
                        updateGalleryInput();
                    } else {
                        alert('Upload failed: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred during upload. Please try again.');
                }
            });
        }
    }

});

@extends('layouts.admin-master', ['title' => 'Users'])

@section('content')
 <style>
    .hidden {
        display:none;
    }
    .img-circle {
        border-radius: 50%;
        width: 50px;
        height: 50px;
    }
    .loader-container {
        display: flex;
        justify-content: center;
        align-items: center;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.6); /* Adjusted to be lighter */
        z-index: 9999;
    }
    .hidden {
        display: none;
    }
    .card {
        position: relative;
    }
    #selectMultiple:checked{
        background-color: #727272;
        border-color: #f3f4f5;
        box-shadow: 0 2px 4px 0 rgb(21 80 174 / 40%);
        accent-color: green;
    }
    
    .btn.btn-danger{
    background: #ff000026;
    border: 1px solid #ffffff;
    color: #ff0000;
    max-width: 109px;
    box-shadow: none;
    }
    
    .user-btn {
    color: #fff;
    background-color: #1550AE;
    border-color: #1550AE;
    box-shadow: 0 0.125rem 0.25rem 0 rgba(105, 108, 255, 0.4);
    padding: 0.4375rem 1.25rem;
    font-size: 0.9375rem;
    border: 1px solid transparent;
    border-radius: 0.375rem;
    transition: all 0.2s ease-in-out;
    }
    
    .user-btn:hover {
    color: #1550AE;
    background-color: #fff;
    border-color: #1550AE;
    transform: translateY(-1px);
    }
    
    
    
    
</style>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                    
                <div id="card-loader" class="loader-container">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <h5 class="card-header d-flex align-items-center justify-content-end"> <a href="{{ route('user.create') }}"
                    class="au-btn--green user-btn m-b-9">Add
                    User</a></h5>
                    
                    <!--<a id="deleteUsers" class='btn btn-danger ml-3 text-white' title="Delete Selected" style="display: none; width: 130px;margin: 0px 20px; text-center" href="javascript:void(0);" onclick="deleteSelectedUsers()">-->
                    <!--    <i class="bx bx-trash me-1 text-white"></i> Delete-->
                    <!--</a>-->
                    
                        
                    <!--<button id="deleteUsers" style="display: none;" onclick="deleteSelectedUsers()">-->
                    <!--    <i class="bx bx-trash me-1" style="color:red;"></i> Delete Selected-->
                    <!--</button>-->
                                        
                    
                    <div class="mb-3 mx-3 d-flex justify-content-between align-items-end">
                        <div>
                            <button id="deleteUsers" style="display:none;" class="btn btn-danger mx-3 align-items-center gap-2 justify-content-center" href="javascript:void(0);" onclick="deleteSelectedUsers()"><i class='bx bx-trash'></i> Delete</button>
                        </div>
                        
                        <label class="d-flex align-items-center">Search:
                        <input type="search" id="search-input" class="form-control" style="margin-left: .5rem;" placeholder="Search by name or email">
                        </label>
                        
                        
                    </div>
                    
                    
                   
                    
                <div class="table-responsive text-nowrap">
                   @if (session('success'))
                        <div class="alert alert-success" id="flashSuccessMessage">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="alert alert-success hidden" id="successMessage">
                        <span></span>
                    </div>
                    <div class="card-datatable table-responsive pb-0">
                        <table class="datatables-basic table border-top" >
                            <thead 
                                <tr>
                                    <th>
                                        <input class="form-check-input border-dark" type="checkbox" id="selectMultiple" />
                                        <label class="form-check-label" for="selectMultiple"></label>
                                    </th>
                                    <th>S.No.</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subscriptions</th>
                                    <th>Subscriptions Expiry Date</th>
                                    <!--<th>Status</th>-->
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="user-list">
                                
                            </tbody>
                        </table>
                         <div class="d-flex justify-content-between mx-3 mt-4 ">
                            <div class="pg-one">
                        <span id="entries-info">Showing 1 to 10 of 100 entries</span>
                        </div>
                            <div>
                            <div id="pagination-container"></div>
                        </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<div id="subscribeModel" class="modal fade" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="subscribeForm">
                @csrf
                <div class="modal-header">
                     <h5 class="modal-title" id="subscribeModelLabel">Please Subscribe</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="noOfDays" class="form-label">Please set an expiration date for the free subscription</label>
                        <input type="hidden" name="user_id" >
                        <input type="date" class="form-control px-2" id="noOfDays" name="noOfDays">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn bg-info border-radius-section color-white">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div id="subscriptionsModal" class="modal fade" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="subscriptionsModalLabel">Subscriptions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul id="subscriptionsList"></ul>
                </div>
            </div>
        </div>
</div>


<script type="text/javascript">
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
        $(window).on('load', function() {
            $('#card-loader').fadeOut('slow');
        });
        
        $(document).ready(function() {
                let currentPage = 1; // Initial page
                const pagesToShow = 5; 
        
            // Hide the session success message after 2 seconds
            if ($("#flashSuccessMessage").length) {
                setTimeout(function() {
                    $("#flashSuccessMessage").fadeOut("slow", function() {
                        $(this).remove();
                    });
                }, 2000); // 2 seconds
            }
        
            // Hide dynamically added success message after 2 seconds
            if ($("#successMessage").text().trim()) {
                $("#successMessage").removeClass("hidden");
                setTimeout(function() {
                    $("#successMessage").fadeOut("slow", function() {
                        $(this).addClass("hidden").removeAttr("style"); // Hide and reset inline styles
                    });
                }, 2000); // 2 seconds
            }
        
            // Set the minimum date for the #noOfDays input field
            var today = new Date().toISOString().split('T')[0];
            $('#noOfDays').attr('min', today);
            
            
        function fetchUserList(page = 1) {
            let searchTerm = $('#search-input').val();
            $.ajax({
                url: "{{ route('get-user-list') }}",
                type: "GET",
                 data: { 
                    page: page, 
                    search: searchTerm
                },
                beforeSend: function() {
                    $('#card-loader').show();
                },
                success: function(response) {
                    $('#card-loader').hide();
                    if (response.status === 200) {
                        let users = response.data.data; // paginated data
                        let html = '';
                        let serialNo = (response.data.current_page - 1) * response.data.per_page;
                        
                        if (users.length > 0) {
                            users.forEach(function(user, index) {
                                let dropdownMenu = '';
                                let userActions = '';
                                console.log(user)
                                if (user.deleted_at == null) {
                                     const editUrl = `/user/${user.id}/edit`;
                                    if (user.role_id != 1) {
                                        userActions += `<a class="dropdown-item" href="{{ route('viewUserDeatils', '') }}/${user.id}">
                                                            <i class="bx bx-detail me-1"></i> View
                                                        </a>`;
                                        if (user.subscription === null) {
                                            userActions += `<a class="dropdown-item" href="javascript:void(0)"
                                                                data-user-id="${user.id}" data-toggle="modal"
                                                                data-target="#subscribeModel"
                                                                onclick="opensubscribeModel('${user.id}')">
                                                                <i class="bx bx-crown me-1"></i> Free Subscribe
                                                            </a>`;
                                        } else if (user.subscription.platform == 'web') {
                                            userActions += `<a class="dropdown-item" href="javascript:void(0)"
                                                                data-user-id="${user.id}" data-toggle="modal"
                                                                data-target="#subscribeModel"
                                                                onclick="cancelsubscribeModel('${user.subscription.id}')">
                                                                <i class="bx bx-x me-1"></i> Cancel Subscribe
                                                            </a>`;
                                        }
                                    }
                                    userActions += `<a class="dropdown-item" href="${editUrl}">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </a>
                                                    <a class="dropdown-item" href="javascript:void(0);" onclick="deleteUser('${user.id}')">
                                                        <i class="bx bx-trash me-1"></i> Delete
                                                    </a>`;
                                    dropdownMenu = `<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu">${userActions}</div>`;
                                } else {
                                    dropdownMenu = `<span style="color: red;">Deleted</span>`;
                                }
        
                                html += `<tr>
                                    <td>
                                        <input name="userIds" value="${user.id}" class="form-check-input child-checkbox border-dark" type="checkbox" id="options${user.id}" />
                                        <label class="form-check-label" for="options${user.id}"></label>
                                    </td>
                                    <td>${serialNo + index + 1}</td>
                                    <td>
                                        <img src="${user.image ? user.image : '/assets/img/famcam.jpg'}" alt="User Image" class="img-circle">
                                        &nbsp;&nbsp;${(user.first_name || user.last_name) ? `${user.first_name || ''} ${user.last_name || ''}`.trim() : 'N/A'}
                                    </td>
                                    <td>${user.email ?? 'N/A'}</td>
                                    <td>${user.subscription ? 
                                        `<a href="javascript:void(0)" class="view-subscriptions" data-user-id="${user.id}" onclick='openModal(${JSON.stringify(user.subscription)})'>View Subscriptions</a>` 
                                        : 'No subscriptions'}</td>
                                    <td>${user.subscription && user.subscription.expiry_date ? new Date(user.subscription.expiry_date).toLocaleDateString() : '-'}</td>
                                    <td>
                                        <div class="dropdown">
                                            ${dropdownMenu}
                                        </div>
                                    </td>
                                </tr>`;
                            });
                            $('#user-list').html(html);
                            
                            attachEventListeners();
                            resetUIState();
                            // Update pagination and entry info
                            updatePagination(response.data.current_page, response.data.last_page);
                            updateEntryInfo(response.data.current_page, response.data.per_page, response.data.total);
                        } else {
                            $('#user-list').html('<tr><td colspan="7" class="text-center">No users found.</td></tr>');
                        }
                    } else {
                        $('#user-list').html('<tr><td colspan="7" class="text-center">Failed to load data.</td></tr>');
                    }
                },
                error: function(error) {
                    $('#card-loader').hide();
                    console.log(error);
                }
            });
            
        }
        
            /*<td>
            ${user.role_id == 3 ? 
            // `<div class="switch">
            //     <input id="cmn-toggle-${user.id}" data-userId="${user.id}" class="cmn-toggle cmn-toggle-round" name="status" type="checkbox" ${user.is_approved ? 'checked' : ''}>
            //     <label for="cmn-toggle-${user.id}"></label>
            // </div>` 
            // : '-'}
            </td>*/
        
        // Resets header checkbox and delete button
        function resetUIState() {
            const headerCheckbox = document.getElementById('selectMultiple');
            const deleteButton = document.getElementById('deleteUsers');
            
            if (headerCheckbox) {
                headerCheckbox.checked = false;
                headerCheckbox.indeterminate = false;
            }
            
            if (deleteButton) {
                deleteButton.style.display = 'none'; // Hide delete button
            }
        }
        
        // Call this function to attach all necessary event listeners to the newly generated content
    function attachEventListeners() {
        // Header checkbox for select-all
        const headerCheckbox = document.getElementById('selectMultiple');
        const childCheckboxes = document.querySelectorAll('.child-checkbox');
        const deleteButton = document.getElementById('deleteUsers');
    
        function updateHeaderCheckbox() {
            const totalCheckboxes = childCheckboxes.length;
            const checkedCheckboxes = Array.from(childCheckboxes).filter(checkbox => checkbox.checked).length;
    
            if (checkedCheckboxes === totalCheckboxes) {
                headerCheckbox.checked = true;
                headerCheckbox.indeterminate = false;
            } else if (checkedCheckboxes > 0) {
                headerCheckbox.indeterminate = true;
                headerCheckbox.checked = false;
            } else {
                headerCheckbox.checked = false;
                headerCheckbox.indeterminate = false;
            }
    
            deleteButton.style.display = checkedCheckboxes > 0 ? 'inline-block' : 'none';
        }
    
        headerCheckbox.addEventListener('change', function () {
            const isChecked = this.checked;
            childCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateHeaderCheckbox();
        });
    
        childCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateHeaderCheckbox);
        });
    }



        
        
        
        $('#search-input').on('input', function() {
            fetchUserList(); // Call fetchUserList to reload data based on the search term
        });
    
    function updatePagination(currentPage, lastPage) {
        let startPage = Math.floor((currentPage - 1) / pagesToShow) * pagesToShow + 1;
        let endPage = Math.min(startPage + pagesToShow - 1, lastPage);
        
        let paginationHTML = '<nav aria-label="Page navigation"><ul class="pagination">';
        
        // Previous button
        paginationHTML += '<li class="page-item' + (currentPage === 1 ? ' disabled' : '') + '">';
        paginationHTML += '<a class="page-link" href="#" data-page="' + (currentPage - 1) + '">Previous</a></li>';
        
        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += '<li class="page-item' + (i === currentPage ? ' active' : '') + '">';
            paginationHTML += '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
        }
        
        // Next button
        paginationHTML += '<li class="page-item' + (currentPage === lastPage ? ' disabled' : '') + '">';
        paginationHTML += '<a class="page-link" href="#" data-page="' + (currentPage + 1) + '">Next</a></li>';
        
        paginationHTML += '</ul></nav>';
        
        $('#pagination-container').html(paginationHTML);
    }

    function updateEntryInfo(currentPage, perPage, totalEntries) {
        let startEntry = ((currentPage - 1) * perPage) + 1;
        let endEntry = Math.min(currentPage * perPage, totalEntries);
        $('#entries-info').text(`Showing ${startEntry} to ${endEntry} of ${totalEntries} entries`);
    }

    // Handle pagination link clicks
    $(document).on('click', '#pagination-container .page-link', function(event) {
        event.preventDefault();
        let page = $(this).data('page');
        if (page) {
            fetchUserList(page);
        }
    });

    // Initial load
    fetchUserList();   
        
            
        });

    
    function opensubscribeModel(id){
        $('input[name="user_id"]').val(id);
        $("#subscribeModel").modal('show');
    }
    
    
    function cancelsubscribeModel(id){
        console.log("id==>",id);
        if (id !== '') {
            swal.fire({
                title: "Are you sure?",
                text: "You want to cancel the subscription for free.",
                icon: "warning",
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Okay',
                dangerMode: true,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('cancel-free-subscription', '') }}/" + id,
                        type: "post",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: {
                            id: id,
                        },
                        success: function(response) {
                            location.reload();
                            var element = document.getElementById("successMessage");
                            element.classList.remove("hidden");
                            element.textContent = "User deleted Successfully.";
                             setTimeout(function() {
                                element.classList.add("hidden");
                                location.reload(); // Refresh the page
                            }, 2000);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            location.reload();
                        }
                    });
                }
            });
        }
    }
    
    
    $('#subscribeForm').on('submit', function(e) {
                e.preventDefault();
                var hasError = false;

                // Clear any existing error messages
                $('.is-invalid').removeClass('is-invalid');
                $('.error-message').remove();
        
                const noOfDays = $('input[name="noOfDays"]').val();
                const id = $('input[name="user_id"]').val();
        
                // Validate Name
                if (!noOfDays) {
                    $('input[name="noOfDays"]').addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please enter No of Days.</span>')
                        .insertAfter($('input[name="noOfDays"]'));
                    hasError = true;
                }
                
                if(!id){
                    $('input[name="user_id"]').addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please Refresh At the location.</span>')
                        .insertAfter($('input[name="user_id"]'));
                    hasError = true;
                }
        
        
                if (!hasError) {
                        let formData = new FormData(this);
                        $.ajax({
                            url: "{{ route('free-subscription', '') }}/" + id,
                            type: "POST",
                            data: formData,
                            contentType: false,
                            processData: false,
                            success: function(response) {
                                // console.log(response);
                                
                                $('.invalid-feedback').remove();
                                $('input, select').removeClass('is-invalid');
                                $("#subscribeModel").modal('hide');
                                const element = document.getElementById('successMessage');
                                element.classList.remove("hidden");
                                element.textContent = response.message;
                                setTimeout(function(){
                                    element.classList.add('hidden');
                                        location.reload();
                                },2000);
                               
                            },
                            error: function(xhr, status, error) {
                                if (xhr.status === 422) {
                                    let errors = xhr.responseJSON.errors;
                                    let errorMessages = [];
        
                                    for (let field in errors) {
                                        if (errors.hasOwnProperty(field)) {
                                            errors[field].forEach(message => {
                                                errorMessages.push(message);
                                            });
                                        }
                                    }
        
                                    // Function to show each error one by one
                                    const showErrorsSequentially = (index = 0) => {
                                        if (index < errorMessages.length) {
                                            swal({
                                                text: "Validation Error",
                                                title: errorMessages[index],
                                                icon: "error",
                                                button: "Ok",
                                            }).then(() => {
                                                // Show the next error after the current one is dismissed
                                                showErrorsSequentially(index + 1);
                                            });
                                        }
                                    };
        
                                    // Start showing errors
                                    showErrorsSequentially();
        
                                } else {
                                    alert('Something went wrong, please try again.');
                                }
                            }
                        });
                }
            });
    
    
    
    function openModal(subscriptions) {
        const modal = $('#subscriptionsModal');
        const subscriptionsList = $('#subscriptionsList');
        subscriptionsList.empty();
        const subscriptionDetails = `
            <div>
                <strong>Subscription:</strong> ${subscriptions.subscription}<br>
                <strong>Platform:</strong> ${subscriptions.platform}
            </div>
        `;
        subscriptionsList.append('<li>' + subscriptionDetails + '</li>');

        modal.modal('show');
    }
    $('#closeModalButton').click(function() {
        $('#subscriptionsModal').modal('hide');
    });


    function deleteUser(id) {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (id !== '') {
            swal.fire({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover!",
                icon: "warning",
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Okay',
                dangerMode: true,
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('hekljlkjlkjlkjlk',result.isConfirmed);
                    $.ajax({
                        url: "{{ route('user.softdelete') }}",
                        type: "post",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: {
                            id: id,
                        },
                        success: function(response) {
                            location.reload();
                            var element = document.getElementById("successMessage");
                            element.classList.remove("hidden");
                            element.textContent = "User deleted Successfully.";
                             setTimeout(function() {
                                element.classList.add("hidden");
                                location.reload(); // Refresh the page
                            }, 2000);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                        console.log("hello this is error",jqXHR, textStatus, errorThrown);
                            // location.reload();
                        }
                    });
                }
            });
        }
    }
    

                                                                           
    $(document).on('click', '.cmn-toggle', function() {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        
        const $checkbox = $(this);
        // Get the userId from the data attribute
        const userId = $checkbox.data('userid');
        // Log or use the userId as needed
        console.log('User ID:', userId); 
        
        // Determine the action based on whether the toggle is checked or not
        const isChecked = $(this).is(':checked');
        const action = isChecked ? 'approve' : 'unapprove';
        const actionText = isChecked ? "approve this advertiser?" : "unapprove this advertiser?";
        const successTitle = isChecked ? "Approved!" : "Unapproved!";
        const successText = isChecked ? "Your action has been approved." : "Your action has been unapproved.";
    
        swal.fire({
            title: "Are you sure?",
              text: `Do you really want to ${actionText}`,
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#3085d6",
              cancelButtonColor: "#d33",
              confirmButtonText: `Yes, ${actionText}!`
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                        url: "{{ route('approveAdvertiser') }}",
                        type: "post",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: {
                            id: userId,
                        },
                        success: function(response) {
                            swalWithBootstrapButtons.fire({
                                title: successTitle,
                                text: successText,
                                icon: "success",
                                timer: 5000,
                            });
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            // location.reload();
                        }
                    });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                if (isChecked) {
                    $(this).prop('checked', false);
                }
                if (!isChecked) {
                    $(this).prop('checked', true);
                }
                swalWithBootstrapButtons.fire({
                    title: "Cancelled",
                    text: "Your action was not changed.",
                    icon: "error",
                    timer: 5000,
                });
            }
        });
    });

    //multi delete
    // Get elements: Header checkbox, child checkboxes, delete button
    // Function to add event listeners after dynamic content is loaded
    
     document.addEventListener('DOMContentLoaded', function() {
   
     });


function deleteSelectedUsers() {
  const selectedUserIds = Array.from(document.querySelectorAll('.child-checkbox:checked'))
    .map(checkbox => checkbox.value);

  if (selectedUserIds.length > 0) {
      deleteUser(selectedUserIds);
    console.log('Selected User IDs for deletion:', selectedUserIds);
    // Here, handle the deletion logic
  } else {
    // alert('No users selected!');
    Swal.fire({
        icon: 'error',
        title: 'No Selection',
        text: 'Please select at least one data to delete.',
        timer: 2000,
        showConfirmButton: false
    });
  }
}


    
</script>

@endsection

@extends('layouts.advertiser-master', ['title' => 'View All Payments','previous'=> '/my-account'])

@section('content')

<style>
    .viewall table tbody tr td{
        text-align: justify;
    }
    .table thead th {
        padding: 0.75rem 1.5rem 0.75rem 1rem;
    }
</style>


 <div class="row">
    <div class="col-md-12 position-relative">
        <div id="preloaders" class="preloader"></div>
        <div class="paymentBox mt-0 px-4 viewall">
            <h5>Payment</h5>
              <div class="form-outline mb-4" data-mdb-input-init>
                <input type="search" class="form-control" placeholder="Search" name="search" />
              </div>              
            <div id="dynamicSearch">
            <table class="table" cellspacing="5">
              <thead>
                <tr>
                  <th scope="col">Ad Name</th>
                  <th scope="col">Date</th>
                  <th scope="col">Amount</th>
                  <th scope="col">Renewal Date</th>
                  <!--<th scope="col">&nbsp;</th>-->
                </tr>
              </thead>
              <tbody>
                @if($transactionData && $transactionData->isNotEmpty())
                    @foreach($transactionData as $data)
                        <tr>
                            <td>{{ $data->ad['ad_name'] ?? 'N/A'}}</td>
                            <td>{{ \Carbon\Carbon::parse($data->ad['start_date'])->format('m/d/y') ?? 'N/A'}}</td>
                            <td>${{  number_format((float)$data->amount, 2, '.', '') ?? 'N/A'}}</td>
                            <td>{{ isset($data->ad['renew_date']) ? \Carbon\Carbon::parse($data->ad['renew_date'])->format('m/d/y') : '-' }}</td>
                            <!--<td>-->
                            <!--    &nbsp;-->
                            <!--</td>-->
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" style="text-align: center;">No Transaction History</td>
                    </tr>
                @endif
              </tbody>
            </table>
            {{ $transactionData->links('pagination::bootstrap-5') }}
            </div>
          </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script>
   

    const search = document.querySelector('input[name="search"]');
    // console.log(search);
    

    const debounceSearch = (func, delay) => {
        let debounceTimer
        return function () {
            const context = this
            const args = arguments
            clearTimeout(debounceTimer)
            debounceTimer = setTimeout(() => func.apply(context, args), delay)
        }
    }
    
    search.addEventListener("input", debounceSearch(function () {
    if(this.value !== ''){ 
        $.ajax({
            method:"GET",
            url:"{{ route('search-payment')  }}",
            data:{search:this.value},
            success:function(response){
                // console.log(response);
                
                const dynamicSearch = document.getElementById('dynamicSearch');
                dynamicSearch.innerHTML = response.data;
            },
            error: function(xhr, status, error) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let errorMessages = [];
                            
                            for (let field in errors) {
                                if (field == 'amount') {
                                    $('<span class="error-message" style="color: red;font-weight: 400;">Please select a Plan.</span>').insertAfter($("#featuredCompanyPrice"));
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
                            // alert('Something went wrong, please try again.');
                        }
                    }
        });
    }else{
        location.reload();   
    }    
    },800));
</script>  

@endsection
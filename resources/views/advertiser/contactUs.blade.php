@extends('layouts.advertiser-master', ['title' => 'Contact Us','previous'=> '/advertiser/dashboard'])

@section('content')

<style>
    input ,textarea{
       font-size: 14px;
      border-radius: 9px;
      color:#000;
    }
     .instructionContainer{
        padding:10px;
        margin-bottom:10px;
        border-radius:10px;
    }
    .trustedPartnerInstructions {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}
    
    .card-info {
        padding-left: 50px;
    padding-top: 13px;
    margin-top: 25px;
    }
    
    .card-contact {
        display: flex;
    margin: 2rem 0;
    }
    
    .color-con {
        border-radius: 10px;
    }
</style>

<div class="row px-xl-4">
          <div class="col-xl-12 col-sm-6 mb-xl-0 mb-4 position-relative">
              <div id="preloaders" class="preloader"></div>
            <div class="instructionContainer card">
                <p class="trustedPartnerInstructions">Do you have any questions or concerns?  Need help with an order or help placing an ad?  Let us know in the form below how we can help you!</p>
            </div>
            <div  id="basic-info">
                
             
<form method="POST" class="contact-us-form" enctype="multipart/form-data" action="{{ route('contactUs') }}">
    @csrf
    <div class=" pb-4 card color-con mt-4">
           @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
        <div class="row">
            <div class="card-contact">
            <div class="col-md-6">
                <div style="padding: 0 1rem; width;90%">
                <div class="field-wrap mb-3">
                     <label class="">Name</label>
                    <input type="text" class="@error('name') is-invalid @enderror form-control" placeholder="Enter your Name" name="name" />
                @error('name')
                    <span class="help-block invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
               </div>
               
                 <div class="field-wrap mb-3">
                    <label class="">Email Address</label>
                    <input type="email" class="@error('email') is-invalid @enderror form-control" placeholder="Enter Email Address" name="email" />
                 @error('email')
                    <span class="help-block invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                   </div>
                   
                   <div>
                    <input type="submit" value="Submit" style="width: 20%;" class="btn mb-0 bg-info border-radius-section color-white mt-4" />
                    </div>
                    </div>
                
                
                
                
                
         <!--      <div class="card-info card"> -->
         <!--        <div class="field-wrap mb-3">-->
         <!--            <label class="">Name</label>-->
         <!--           <input type="text" class="@error('name') is-invalid @enderror form-control" placeholder="Enter your Name" name="name" />-->
         <!--       @error('name')-->
         <!--           <span class="help-block invalid-feedback" role="alert">-->
         <!--               <strong>{{ $message }}</strong>-->
         <!--           </span>-->
         <!--       @enderror-->
         <!--      </div>-->
               
         <!--       <div class="field-wrap mb-3">-->
         <!--           <label class="">Phone</label>-->
         <!--           <input type="text" class="@error('phone') is-invalid @enderror form-control" placeholder="Enter Mobile No." name="phone" />-->
         <!--       @error('phone')-->
         <!--           <span class="help-block invalid-feedback" role="alert">-->
         <!--               <strong>{{ $message }}</strong>-->
         <!--           </span>-->
         <!--       @enderror-->
         <!--    </div>-->
             
         <!--     <div class="field-wrap mb-3">-->
         <!--           <label class="">Email Address</label>-->
         <!--           <input type="email" class="@error('email') is-invalid @enderror form-control" placeholder="Enter Email Address" name="email" />-->
         <!--        @error('email')-->
         <!--           <span class="help-block invalid-feedback" role="alert">-->
         <!--               <strong>{{ $message }}</strong>-->
         <!--           </span>-->
         <!--       @enderror-->
         <!--          </div>-->
         <!--       <div class="field-wrap mb-3">-->
         <!--           <label class="">Field Label</label>-->
         <!--           <textarea name="label" class="@error('label') is-invalid @enderror"  placeholder="Placeholder text"></textarea>-->
         <!--    @error('label')-->
         <!--           <span class="help-block invalid-feedback" role="alert">-->
         <!--               <strong>{{ $message }}</strong>-->
         <!--           </span>-->
         <!--       @enderror-->
         <!--</div>-->
         <!--  <div>-->
         <!--       <input type="submit" value="Submit" class="btn mb-0 bg-info border-radius-section w-100 color-white mt-4" />-->
         <!--  </div>-->
         <!--  </div>-->
        </div>
        
           <div class="col-md-6">
               <div style="padding: 0 1rem; width;90%">
            <div class="field-wrap mb-3">
                    <label class="">Phone</label>
                    <input type="text" class="@error('phone') is-invalid @enderror form-control" placeholder="Enter Mobile No." name="phone" />
                @error('phone')
                    <span class="help-block invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
             </div>
             
             <div class="field-wrap mb-3">
                    <label class="">Message</label>
                    <textarea name="label" class="@error('label') is-invalid @enderror"  placeholder="Enter Message" style="min-height: 8rem;"></textarea>
             @error('label')
                    <span class="help-block invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
         </div>
         </div>
        </div>
        </div>
        </div>
</form>

            </div>
            </div>
        </div>
          </div>
@endsection
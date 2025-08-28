@extends('layouts.advertiser-master', ['title' => 'Orders','previous' => '/orders'])

@section('content')
<style>
    
        /*table,*/
        /*td,*/
        /*th {*/
        /*    border: 1px solid black;*/
        /*    border-collapse: collapse;*/
        /*}*/

        /*table {*/
        /*    width: 700px;*/
        /*    margin-left: auto;*/
        /*    margin-right: auto;*/
        /*}*/

        /*td,*/
        /*caption {*/
        /*    padding: 16px;*/
        /*}*/

        /*th {*/
        /*    padding: 16px;*/
        /*    background-color: #2d3246;*/
        /*    text-align: left;*/
        /*    color:#fff;*/
        /*}*/
        /*table  tr th img{*/
        /*    max-width:100px;*/
        /*}*/
        
        /*#invoiceDateContainer{*/
        /*    min-width:160px;*/
        /*}*/
        
        /*@media print{*/
        /*    @page {*/
        /*        size: auto;*/
        /*        margin: 0;*/
        /*    }*/
        /*    body{*/
        /*        -webkit-print-color-adjust:exact !important;*/
        /*        print-color-adjust:exact !important;*/
        /*    }*/
        /*    .navbar{*/
        /*        display:none;*/
        /*    }*/
        /*    #invoice,*/
        /*    #invoice tr,*/
        /*    #invoice tr th,*/
        /*    #invoice tr td{*/
        /*        width:100%;*/
        /*    }*/
        /*    th {*/
        /*        print-color-adjust: exact;*/
        /*        padding:16px;*/
        /*        margin:0px;*/
        /*        background-color: #2d3246;*/
        /*        text-align: left;*/
        /*        color:#fff;*/
        /*    }*/
            
        /*    #invoice>*{*/
        /*        display:block;*/
        /*    }*/
            
        /*    #invoice tfoot{*/
        /*        display:none;*/
        /*    }*/
            
        /*    #invoiceDateContainer{*/
        /*        min-width:200px;*/
        /*    }*/
           
        /*}*/
       
       @media print{
            @page {
                size: auto;
                margin: 0;
            }
            body{
                -webkit-print-color-adjust:exact !important;
                print-color-adjust:exact !important;
            }
            #invoice{
                height:100%;
            }
            
            .navbar{
                display:none;
            }
            #invoice tfoot{
                display:none;
            }
       }
</style>


     
<div class="row">
    <div class="col-12">
        <table id="invoice" style="width:100%;max-width:750px;margin:auto;background:#fff">
              <thead>
                  <tr>
                  <th style="padding: 15px;" class="text-center">
                      <span style="background: #2d3246;padding: 15px;border-radius:10px;display: block;width: fit-content;">
                          <img width="80" src="{{ asset('advertiser/img/logo-ct.png') }}" >
                      </span>
                  </th>
                   <th style="padding: 15px;text-align:right;" >
                      <span style="color: #1a73e8;font-size: 25px;font-weight: 700;">INVOICE</span><br/>
                      <span style="font-weight: 600;color: #000;font-size: 14px;">
                          Order Id: {{ $invoice->order_id }}</br>
                          {{ date('d F Y',strtotime($invoice->created_at))}}
                      </span>
                  </th>
              </tr>
               <tr>
            <td style="padding: 15px;text-align:left;">
              <strong style="color: #1a73e8;font-size: 16px;">Pay To</strong> <br> 
              <span style="font-weight: 600;color: #000;font-size: 14px;display: block;line-height: 1.2;padding: 5px 0;">
                  Famory <br>
              123 Maple St. <br>
              Knoxville, TN 37922
              </span>
            </td>
            
            <td style="padding: 15px;text-align:right;">
              <strong style="color: #1a73e8;font-size: 16px;">Customer</strong> <br>
              <span style="font-weight: 600;color: #000;font-size: 14px;display: block;line-height: 1.2;padding: 5px 0;">
                   {{ $invoice->user->first_name." ".$invoice->user->last_name  }} <br>
                   {{ $invoice->address->house_number }} <br> {{ $invoice->address->road_name }} <br>
                   {{ $invoice->address->state }} {{ $invoice->address->zip_code }}
              </span>
            </td>
            
          </tr>
              </thead>
              <tbody>
                  <tr>
                      <td colspan="2" style="padding: 15px;">
                          <table style="width:100%">
                               <tr style="background: #2d3246;">
            <th style="padding: 10px;color:#fff;text-align:left;">Name</th>
            <th style="padding: 10px;color:#fff;text-align:center;">Qty.</th>
            <th style="padding: 10px;color:#fff;text-align:center;">MRP</th>
            <th style="padding: 10px;color:#fff;text-align:right;">Amount</th>
          </tr>
          @php
            $grTotal = 0;
          @endphp
          @foreach($invoice->products as $product)
              <tr style="background: #eeeeee;border-bottom: 1px solid #888;">
                  <td style="padding: 10px;text-align:left;color:#000;">{{ $product->name }}</td>
                  <td style="padding: 10px;text-align:center;color:#000;">{{ $invoice->quantity }}</td>
                  <td style="padding: 10px;text-align:center;color:#000;">${{ number_format((float)$product->price, 2, '.', '') }}</td>
                  <td style="padding: 10px;text-align:right;color:#000;">$@php 
                  $total = $invoice->quantity * $product->price;
                  echo number_format((float)$total, 2, '.', '');
                  $grTotal +=$total;  @endphp</td>
              </tr>
          @endforeach
          
          <tr>
            <th colspan="4"  style="padding: 5px;text-align:right;color:#000;font-size:18px;">Subtotal: <span style="color: #1a73e8;">${{ number_format((float)$grTotal, 2, '.', '') }}</span></th>
          </tr>
          
          <tr>
            <th colspan="4"  style="padding: 5px;text-align:right;color:#000;font-size:18px;">Grand Total: <span style="color: #1a73e8;">${{ number_format((float)$grTotal, 2, '.', '')  }}</span></th>
          </tr>
                          </table>
                      </td>
                  </tr>
              </tbody>
          <tfoot>
              <tr>
                 <td colspan="4" style="padding: 15px;text-align:center"><button class="btn btn-sm btn-info" id="printBtn" onclick="printContent()">Print</button></td>
              </tr>
          </tfoot>
        </table>
    </div>
</div>
<script>

    function printContent() {
        window.print();
    }
   
</script>

@endsection
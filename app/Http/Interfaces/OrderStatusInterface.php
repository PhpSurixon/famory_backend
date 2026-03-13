<?php

namespace App\Http\Interfaces;

interface OrderStatusInterface
{
	const Pending 			= 1;
	const Confirmed 		= 2;
	const Shipped 		    = 3;
	const Delivered 		= 4;
	const Not_Delivered 	= 5;
	const Cancelled 		= 6;
	const Failed 		    = 7; // payment failed / expired unpaid
	const Refunded          = 8; // cancelled + stripe refund processed



}

 			
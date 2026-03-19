<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Invoice</title>
		<link rel="stylesheet" href="{{css-url}}">
		<link rel="license" href="http://www.opensource.org/licenses/mit-license/">		
	</head>
	<body>
		<header>
			<h1>Invoice</h1>
			<address contenteditable>
				<p>Your company name</p>
				<p>Address line 1<br>Address line 2</p>
				<p>phone</p>
			</address>
			<span><h1>LOGO</h1></span>
		</header>
		<article>
			<h1>Recipient</h1>
			<address contenteditable>
				<p>{{client-name}}</p>
			</address>
			<table class="meta">
				<tr>
					<th><span contenteditable>Invoice #</span></th>
					<td><span contenteditable>{{invoice-num}}</span></td>
				</tr>
				<tr>
					<th><span contenteditable>Date</span></th>
					<td><span contenteditable>{{invoice-date}}</span></td>
				</tr>
				<tr>
					<th><span contenteditable>Amount Due</span></th>
					<td><span id="prefix" contenteditable>{{currency}}</span><span>{{amount}}</span></td>
				</tr>
			</table>
			<table class="inventory">
				<thead>
					<tr>
						<th><span contenteditable>Item</span></th>
						<th><span contenteditable>Description</span></th>
						<th><span contenteditable>Rate</span></th>
						<th><span contenteditable>Quantity</span></th>
						<th><span contenteditable>Price</span></th>
					</tr>
				</thead>
				<tbody>
					<!-- items -->
					<tr>
						<td><span contenteditable>{{item}}</span></td>
						<td><span contenteditable><!-- item-description-->{{num-beds}} bed booked in {{room-type}} room.<br>
						From date {{from-date}} to date {{to-date}}<br>
						{{addons}}<!-- item-description--></span></td>
						<td><span data-prefix>{{currency}}</span><span contenteditable>{{item-amount}}</span></td>
						<td><span contenteditable>1</span></td>
						<td><span data-prefix>{{currency}}</span><span>{{item-amount}}</span></td>
					</tr>
					<!-- items -->
				</tbody>
			</table>
		
			<table class="balance">
				<tr>
					<th><span contenteditable>Total</span></th>
					<td><span data-prefix>{{currency}}</span><span>{{amount}}</span></td>
				</tr>
				<tr>
					<th><span contenteditable>Amount Paid</span></th>
					<td><span data-prefix>{{currency}}</span><span contenteditable>{{amount-paid}}</span></td>
				</tr>
				<tr>
					<th><span contenteditable>Balance Due</span></th>
					<td><span data-prefix>{{currency}}</span><span>{{amount-due}}</span></td>
				</tr>
			</table>
		</article>
		
	</body>
</html>
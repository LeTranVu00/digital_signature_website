<h1>Lead moi tu website</h1>

<p><strong>Ho ten:</strong> {{ $contact->name }}</p>
<p><strong>Email:</strong> {{ $contact->email }}</p>
<p><strong>Dien thoai:</strong> {{ $contact->phone ?? 'Chua cung cap' }}</p>
<p><strong>Cong ty:</strong> {{ $contact->company ?? 'Chua cung cap' }}</p>
<p><strong>Dich vu:</strong> {{ $contact->serviceLabel() }}</p>
<p><strong>Trang thai:</strong> {{ $contact->statusLabel() }}</p>

<p><strong>Noi dung:</strong></p>
<p>{{ $contact->message }}</p>

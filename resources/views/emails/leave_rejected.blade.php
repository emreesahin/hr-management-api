<h2>İzin Talebi Reddedildi</h2>

<p><strong>Çalışan:</strong> {{ $employee->user->name }} ({{ $employee->user->email }})</p>
<p><strong>Başlangıç Tarihi:</strong> {{ $leave->start_date }}</p>
<p><strong>Bitiş Tarihi:</strong> {{ $leave->end_date }}</p>
<p><strong>Süre:</strong> {{ $leave->duration }} gün</p>
<p><strong>Gerekçe:</strong> {{ $leave->reason }}</p>

@if($status === 'online')
  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-green-900/50 text-green-400 border border-green-800">
    <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Online
  </span>
@elseif($status === 'offline')
  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-red-900/50 text-red-400 border border-red-800">
    <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Offline
  </span>
@else
  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-gray-800 text-gray-500 border border-gray-700">
    <span class="w-1.5 h-1.5 rounded-full bg-gray-600"></span>Unknown
  </span>
@endif
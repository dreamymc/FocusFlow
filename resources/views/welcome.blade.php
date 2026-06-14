<!DOCTYPE html>
<html>
<head>
    <title>FocusFlow WebSocket Demo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center h-screen font-sans">
    <div class="p-8 bg-white rounded-xl shadow-lg text-center max-w-md w-full">
        <h1 class="text-2xl font-bold mb-2 text-slate-800">FocusFlow Demo</h1>
        <p class="text-slate-500 mb-8 text-sm">Testing Workspace ID: 1</p>
        
        <div id="app"></div>
        
        <p class="text-xs text-slate-400 mt-8">
            Trigger a task update via the API or Postman to see this bell update in real-time without refreshing!
        </p>
    </div>
</body>
</html>

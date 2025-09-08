<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Workflow Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Test Workflow Preview</h1>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Simple IOM Test</h2>
            <button id="testBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Test Workflow Preview
            </button>
        </div>
        
        <div id="results" class="bg-white rounded-lg shadow-md p-6 hidden">
            <h3 class="text-lg font-semibold mb-3">Results:</h3>
            <pre id="resultContent" class="bg-gray-100 p-4 rounded text-sm overflow-auto"></pre>
        </div>
    </div>

    <script>
        document.getElementById('testBtn').addEventListener('click', async function() {
            const resultsDiv = document.getElementById('results');
            const resultContent = document.getElementById('resultContent');
            
            try {
                this.textContent = 'Testing...';
                this.disabled = true;
                
                const response = await fetch('/test-workflow-preview-api', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({})
                });
                
                const result = await response.json();
                
                resultContent.textContent = JSON.stringify(result, null, 2);
                resultsDiv.classList.remove('hidden');
                
            } catch (error) {
                resultContent.textContent = 'Error: ' + error.message;
                resultsDiv.classList.remove('hidden');
            } finally {
                this.textContent = 'Test Workflow Preview';
                this.disabled = false;
            }
        });
    </script>
</body>
</html>

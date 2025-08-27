<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management - Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Quote Management System</h1>
            <p class="text-gray-600 mt-2">Login to access the API</p>
        </div>

        <!-- Login Form -->
        <form id="loginForm" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="admin@example.com">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="password">
            </div>

            <button type="submit" id="loginBtn"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Login
            </button>
        </form>

        <!-- Test Accounts -->
        <div class="mt-6 p-4 bg-gray-50 rounded-md">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Test Accounts:</h3>
            <div class="text-xs text-gray-600 space-y-1">
                <div><strong>Admin:</strong> admin@example.com / password</div>
                <div><strong>User:</strong> user@example.com / password</div>
            </div>
        </div>

        <!-- Messages -->
        <div id="message" class="mt-4 hidden"></div>

        <!-- API Test Section -->
        <div id="apiTest" class="mt-6 hidden">
            <h3 class="text-lg font-medium text-gray-900 mb-4">API Test Dashboard</h3>
            
            <div class="space-y-4">
                <button onclick="testProducts()" 
                    class="w-full py-2 px-4 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Test Products API
                </button>
                
                <button onclick="testClients()" 
                    class="w-full py-2 px-4 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                    Test Clients API
                </button>
                
                <button onclick="testQuotes()" 
                    class="w-full py-2 px-4 bg-orange-600 text-white rounded-md hover:bg-orange-700">
                    Test Quotes API
                </button>

                <button onclick="logout()" 
                    class="w-full py-2 px-4 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Logout
                </button>
            </div>

            <div id="apiResults" class="mt-4 p-4 bg-gray-100 rounded-md hidden">
                <h4 class="font-medium text-gray-900 mb-2">API Response:</h4>
                <pre id="apiOutput" class="text-xs text-gray-700 overflow-auto max-h-64"></pre>
            </div>
        </div>
    </div>

    <script>
        let authToken = null;
        const API_BASE = '/api';

        // Login form handler
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            loginBtn.textContent = 'Logging in...';
            loginBtn.disabled = true;

            try {
                const response = await fetch(`${API_BASE}/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (response.ok) {
                    authToken = data.token;
                    showMessage('Login successful!', 'success');
                    document.getElementById('loginForm').style.display = 'none';
                    document.getElementById('apiTest').classList.remove('hidden');
                } else {
                    showMessage(data.message || 'Login failed', 'error');
                }
            } catch (error) {
                showMessage('Network error: ' + error.message, 'error');
            } finally {
                loginBtn.textContent = 'Login';
                loginBtn.disabled = false;
            }
        });

        // Test API functions
        async function testProducts() {
            await makeApiCall('/products', 'GET', 'Products');
        }

        async function testClients() {
            await makeApiCall('/clients', 'GET', 'Clients');
        }

        async function testQuotes() {
            await makeApiCall('/quotes', 'GET', 'Quotes');
        }

        async function makeApiCall(endpoint, method, name) {
            try {
                const response = await fetch(`${API_BASE}${endpoint}`, {
                    method: method,
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                
                document.getElementById('apiResults').classList.remove('hidden');
                document.getElementById('apiOutput').textContent = 
                    `${name} API Response (${response.status}):\n` + 
                    JSON.stringify(data, null, 2);

                if (response.ok) {
                    showMessage(`${name} API test successful!`, 'success');
                } else {
                    showMessage(`${name} API test failed: ${data.message}`, 'error');
                }
            } catch (error) {
                showMessage(`${name} API error: ` + error.message, 'error');
            }
        }

        async function logout() {
            try {
                await fetch(`${API_BASE}/logout`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                });
            } catch (error) {
                console.log('Logout error:', error);
            }

            authToken = null;
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('apiTest').classList.add('hidden');
            document.getElementById('apiResults').classList.add('hidden');
            document.getElementById('message').classList.add('hidden');
            showMessage('Logged out successfully', 'success');
        }

        function showMessage(text, type) {
            const messageEl = document.getElementById('message');
            messageEl.textContent = text;
            messageEl.className = `mt-4 p-3 rounded-md text-sm ${
                type === 'success' 
                    ? 'bg-green-100 text-green-700 border border-green-300' 
                    : 'bg-red-100 text-red-700 border border-red-300'
            }`;
            messageEl.classList.remove('hidden');
        }

        // Pre-fill admin credentials for easy testing
        document.getElementById('email').value = 'admin@example.com';
        document.getElementById('password').value = 'password';
    </script>
</body>
</html>
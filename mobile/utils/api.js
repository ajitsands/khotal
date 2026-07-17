// API Utility for K Hotel Mobile Client

const API_BASE_URL = 'http://localhost:3031/backend/api'; // Replace with server host in production

let userToken = null;

export const setAuthToken = (token) => {
    userToken = token;
};

export const getAuthToken = () => {
    return userToken;
};

// Generic HTTP Request Handler
const request = async (endpoint, method = 'GET', body = null) => {
    const url = `${API_BASE_URL}/${endpoint}`;
    
    const headers = {
        'Content-Type': 'application/json',
    };
    
    if (userToken) {
        headers['Authorization'] = `Bearer ${userToken}`;
    }
    
    const config = {
        method,
        headers,
    };
    
    if (body) {
        config.body = JSON.stringify(body);
    }
    
    try {
        const response = await fetch(url, config);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Something went wrong');
        }
        
        return data;
    } catch (error) {
        console.error(`API Error (${endpoint}):`, error.message);
        throw error;
    }
};

// API Services
export const authService = {
    login: async (loginIdentifier, password) => {
        const response = await request('auth.php?action=login', 'POST', {
            login_identifier: loginIdentifier,
            password
        });
        if (response.success && response.data.token) {
            setAuthToken(response.data.token);
        }
        return response;
    },
    getProfile: async () => {
        return await request('auth.php?action=profile');
    }
};

export const pointsService = {
    getBalance: async () => {
        return await request('points.php?action=balance');
    },
    getHistory: async () => {
        return await request('points.php?action=history');
    },
    redeemAward: async (awardTitle) => {
        return await request('points.php?action=redeem', 'POST', {
            award_title: awardTitle
        });
    },
    getCatalogue: async () => {
        return await request('points.php?action=catalogue');
    }
};

export const vouchersService = {
    getMyVouchers: async () => {
        return await request('vouchers.php?action=my_vouchers');
    }
};

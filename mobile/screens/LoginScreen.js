import React, { useState } from 'react';
import { 
    View, 
    Text, 
    TextInput, 
    TouchableOpacity, 
    StyleSheet, 
    ActivityIndicator, 
    Image, 
    KeyboardAvoidingView, 
    Platform, 
    ScrollView 
} from 'react-native';
import { authService } from '../utils/api';

export default function LoginScreen({ onLoginSuccess }) {
    const [loginId, setLoginId] = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(false);
    const [errorMessage, setErrorMessage] = useState('');

    const handleLogin = async () => {
        if (!loginId || !password) {
            setErrorMessage('Please fill in all fields.');
            return;
        }

        setLoading(true);
        setErrorMessage('');

        try {
            const response = await authService.login(loginId, password);
            if (response.success && response.data) {
                onLoginSuccess(response.data.member, response.data.token);
            } else {
                setErrorMessage(response.message || 'Login failed. Please try again.');
            }
        } catch (error) {
            setErrorMessage(error.message || 'Network error. Please check your connection.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <KeyboardAvoidingView 
            behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
            style={styles.container}
        >
            <ScrollView contentContainerStyle={styles.scrollContainer} keyboardShouldPersistTaps="handled">
                
                {/* Upper Brand Section */}
                <View style={styles.brandContainer}>
                    <View style={styles.logoCircle}>
                        <Text style={styles.logoK}>K</Text>
                    </View>
                    <Text style={styles.brandTitle}>THE K HOTEL</Text>
                    <Text style={styles.brandSubtitle}>LOYALTY MEMBER PASS</Text>
                </View>

                {/* Form Card Section */}
                <View style={styles.formCard}>
                    <Text style={styles.loginTitle}>Welcome Back</Text>
                    <Text style={styles.loginSubtitle}>Sign in to access your discounts and loyalty points</Text>

                    {errorMessage ? (
                        <View style={styles.errorBox}>
                            <Text style={styles.errorText}>{errorMessage}</Text>
                        </View>
                    ) : null}

                    <View style={styles.inputGroup}>
                        <Text style={styles.inputLabel}>EMAIL OR MOBILE NUMBER</Text>
                        <TextInput 
                            style={styles.input}
                            placeholder="e.g. john.doe@example.com"
                            placeholderTextColor="#64748b"
                            value={loginId}
                            onChangeText={setLoginId}
                            autoCapitalize="none"
                            keyboardType="email-address"
                        />
                    </View>

                    <View style={styles.inputGroup}>
                        <Text style={styles.inputLabel}>SECRET PASSWORD / PIN</Text>
                        <TextInput 
                            style={styles.input}
                            placeholder="Enter your security password"
                            placeholderTextColor="#64748b"
                            value={password}
                            onChangeText={setPassword}
                            secureTextEntry
                            autoCapitalize="none"
                        />
                    </View>

                    <TouchableOpacity 
                        style={styles.loginButton} 
                        onPress={handleLogin}
                        disabled={loading}
                    >
                        {loading ? (
                            <ActivityIndicator color="#fff" />
                        ) : (
                            <Text style={styles.loginButtonText}>Sign In</Text>
                        )}
                    </TouchableOpacity>

                    <TouchableOpacity style={styles.forgotBtn}>
                        <Text style={styles.forgotBtnText}>Forgot password or loyalty number?</Text>
                    </TouchableOpacity>
                </View>

                {/* Sign up prompt */}
                <View style={styles.signupPrompt}>
                    <Text style={styles.signupPromptText}>Not a Loyalty Member yet?</Text>
                    <Text style={styles.signupContactText}>Please visit the Hotel Front Desk or contact our Sales office to sign up and get your welcome vouchers instantly!</Text>
                </View>

            </ScrollView>
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#0b0f19',
    },
    scrollContainer: {
        flexGrow: 1,
        justifyContent: 'center',
        padding: 24,
    },
    brandContainer: {
        alignItems: 'center',
        marginBottom: 40,
        marginTop: 20,
    },
    logoCircle: {
        width: 80,
        height: 80,
        borderRadius: 40,
        backgroundColor: 'rgba(217,119,6,0.1)',
        alignItems: 'center',
        justifyContent: 'center',
        borderWidth: 2,
        borderColor: '#fbbf24',
        marginBottom: 16,
    },
    logoK: {
        fontSize: 42,
        fontWeight: '900',
        color: '#fbbf24',
    },
    brandTitle: {
        fontSize: 24,
        fontWeight: '800',
        color: '#fff',
        letterSpacing: 3,
    },
    brandSubtitle: {
        fontSize: 11,
        fontWeight: '600',
        color: '#d97706',
        letterSpacing: 2,
        marginTop: 6,
    },
    formCard: {
        backgroundColor: '#151f32',
        borderRadius: 20,
        padding: 24,
        borderWidth: 1,
        borderColor: '#1e293b',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 10 },
        shadowOpacity: 0.3,
        shadowRadius: 15,
        elevation: 8,
    },
    loginTitle: {
        fontSize: 20,
        fontWeight: '700',
        color: '#fff',
        textAlign: 'center',
    },
    loginSubtitle: {
        fontSize: 13,
        color: '#94a3b8',
        textAlign: 'center',
        marginTop: 6,
        marginBottom: 20,
    },
    errorBox: {
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        borderLeftWidth: 4,
        borderLeftColor: '#ef4444',
        padding: 12,
        borderRadius: 6,
        marginBottom: 16,
    },
    errorText: {
        color: '#f87171',
        fontSize: 13,
        fontWeight: '500',
    },
    inputGroup: {
        marginBottom: 16,
    },
    inputLabel: {
        color: '#94a3b8',
        fontSize: 10,
        fontWeight: '700',
        letterSpacing: 0.5,
        marginBottom: 8,
    },
    input: {
        backgroundColor: '#0b0f19',
        borderWidth: 1,
        borderColor: '#1e293b',
        borderRadius: 10,
        paddingHorizontal: 16,
        paddingVertical: 12,
        color: '#fff',
        fontSize: 14,
    },
    loginButton: {
        backgroundColor: '#d97706',
        borderRadius: 10,
        paddingVertical: 14,
        alignItems: 'center',
        justifyContent: 'center',
        marginTop: 10,
        shadowColor: '#d97706',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 6,
        elevation: 4,
    },
    loginButtonText: {
        color: '#fff',
        fontSize: 16,
        fontWeight: '700',
    },
    forgotBtn: {
        alignItems: 'center',
        marginTop: 16,
    },
    forgotBtnText: {
        color: '#94a3b8',
        fontSize: 12,
        textDecorationLine: 'underline',
    },
    signupPrompt: {
        marginTop: 35,
        alignItems: 'center',
    },
    signupPromptText: {
        color: '#fff',
        fontSize: 14,
        fontWeight: '600',
    },
    signupContactText: {
        color: '#64748b',
        fontSize: 12,
        textAlign: 'center',
        lineHeight: 18,
        marginTop: 6,
        marginHorizontal: 10,
    }
});

import React, { useState } from 'react';
import { SafeAreaView, View, Text, StyleSheet, TouchableOpacity, StatusBar } from 'react-native';
import LoginScreen from './screens/LoginScreen';
import HomeScreen from './screens/HomeScreen';
import VouchersScreen from './screens/VouchersScreen';
import RedemptionScreen from './screens/RedemptionScreen';
import ProfileScreen from './screens/ProfileScreen';
import { setAuthToken } from './utils/api';

export default function App() {
    const [isLoggedIn, setIsLoggedIn] = useState(false);
    const [currentMember, setCurrentMember] = useState(null);
    const [activeTab, setActiveTab] = useState('home'); // 'home', 'vouchers', 'points', 'profile'

    const handleLoginSuccess = (member, token) => {
        setAuthToken(token);
        setCurrentMember(member);
        setIsLoggedIn(true);
        setActiveTab('home');
    };

    const handleLogout = () => {
        setAuthToken(null);
        setCurrentMember(null);
        setIsLoggedIn(false);
    };

    if (!isLoggedIn) {
        return (
            <SafeAreaView style={styles.container}>
                <StatusBar barStyle="light-content" backgroundColor="#0b0f19" />
                <LoginScreen onLoginSuccess={handleLoginSuccess} />
            </SafeAreaView>
        );
    }

    // Render active tab screen
    const renderActiveScreen = () => {
        switch (activeTab) {
            case 'vouchers':
                return <VouchersScreen member={currentMember} />;
            case 'points':
                return <RedemptionScreen member={currentMember} />;
            case 'profile':
                return <ProfileScreen member={currentMember} onLogout={handleLogout} />;
            case 'home':
            default:
                return (
                    <HomeScreen 
                        member={currentMember} 
                        onNavigateToVouchers={() => setActiveTab('vouchers')}
                        onNavigateToPoints={() => setActiveTab('points')}
                    />
                );
        }
    };

    return (
        <SafeAreaView style={styles.container}>
            <StatusBar barStyle="light-content" backgroundColor="#0b0f19" />
            
            {/* Main view container */}
            <View style={styles.contentBody}>
                {renderActiveScreen()}
            </View>

            {/* Premium Gold/Grey Navigation Bar */}
            <View style={styles.bottomNav}>
                <TouchableOpacity 
                    style={styles.navItem} 
                    onPress={() => setActiveTab('home')}
                >
                    <Text style={[styles.navIcon, activeTab === 'home' && styles.activeIcon]}>🏠</Text>
                    <Text style={[styles.navLabel, activeTab === 'home' && styles.activeLabel]}>Home</Text>
                </TouchableOpacity>

                <TouchableOpacity 
                    style={styles.navItem} 
                    onPress={() => setActiveTab('vouchers')}
                >
                    <Text style={[styles.navIcon, activeTab === 'vouchers' && styles.activeIcon]}>🎫</Text>
                    <Text style={[styles.navLabel, activeTab === 'vouchers' && styles.activeLabel]}>Vouchers</Text>
                </TouchableOpacity>

                {currentMember.membership_type === 'K Reward' && (
                    <TouchableOpacity 
                        style={styles.navItem} 
                        onPress={() => setActiveTab('points')}
                    >
                        <Text style={[styles.navIcon, activeTab === 'points' && styles.activeIcon]}>⭐</Text>
                        <Text style={[styles.navLabel, activeTab === 'points' && styles.activeLabel]}>Rewards</Text>
                    </TouchableOpacity>
                )}

                <TouchableOpacity 
                    style={styles.navItem} 
                    onPress={() => setActiveTab('profile')}
                >
                    <Text style={[styles.navIcon, activeTab === 'profile' && styles.activeIcon]}>👤</Text>
                    <Text style={[styles.navLabel, activeTab === 'profile' && styles.activeLabel]}>Profile</Text>
                </TouchableOpacity>
            </View>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#0b0f19',
    },
    contentBody: {
        flex: 1,
    },
    bottomNav: {
        flexDirection: 'row',
        height: 65,
        backgroundColor: '#0e1626',
        borderTopWidth: 1,
        borderColor: '#1e293b',
        paddingBottom: 5,
    },
    navItem: {
        flex: 1,
        alignItems: 'center',
        justifyContent: 'center',
    },
    navIcon: {
        fontSize: 20,
        opacity: 0.4,
    },
    activeIcon: {
        opacity: 1,
    },
    navLabel: {
        fontSize: 10,
        color: '#64748b',
        fontWeight: '600',
        marginTop: 4,
    },
    activeLabel: {
        color: '#fbbf24',
        fontWeight: '700',
    }
});

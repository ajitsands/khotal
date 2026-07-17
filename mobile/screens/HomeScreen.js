import React, { useState, useEffect } from 'react';
import { 
    View, 
    Text, 
    StyleSheet, 
    ScrollView, 
    RefreshControl, 
    TouchableOpacity, 
    ActivityIndicator 
} from 'react-native';
import LoyaltyCard from '../components/LoyaltyCard';
import { vouchersService, pointsService } from '../utils/api';

export default function HomeScreen({ member, onNavigateToVouchers, onNavigateToPoints }) {
    const [refreshing, setRefreshing] = useState(false);
    const [vouchersCount, setVouchersCount] = useState(0);
    const [pointsBalance, setPointsBalance] = useState(0);
    const [loading, setLoading] = useState(true);

    const loadDashboardStats = async () => {
        try {
            // Get active vouchers
            const vResponse = await vouchersService.getMyVouchers();
            if (vResponse.success && vResponse.data.vouchers) {
                const activeVouchers = vResponse.data.vouchers.filter(v => v.status === 'Active');
                setVouchersCount(activeVouchers.length);
            }

            // Get points if Booker
            if (member.membership_type === 'K Reward') {
                const pResponse = await pointsService.getBalance();
                if (pResponse.success && pResponse.data) {
                    setPointsBalance(pResponse.data.current_balance);
                }
            }
        } catch (error) {
            console.log('Error loading stats:', error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadDashboardStats();
    }, []);

    const onRefresh = async () => {
        setRefreshing(true);
        await loadDashboardStats();
        setRefreshing(false);
    };

    // Get list of discounts for the member's card
    const getBenefits = () => {
        if (member.membership_type === 'K Plus') {
            return [
                { icon: '🏨', title: 'Room discount', desc: '10% off best available rate (direct/walk-in)' },
                { icon: '🍽️', title: 'Restaurant & Lounges', desc: '20% off food & beverage bills' },
                { icon: '💪', title: 'Health Club facilities', desc: '20% off gym and facilities usage' },
                { icon: '⛺', title: 'Outdoor Catering', desc: '10% discount on catering services' }
            ];
        } else {
            // K Reward Booker
            return [
                { icon: '⭐', title: 'Earn points', desc: 'Earn points per night on room bookings (1-12 pts)' },
                { icon: '🏢', title: 'Event bookings', desc: 'Get 3 Points for every 70.000 BHD spent' },
                { icon: '🍷', title: 'Dining rewards', desc: 'Get 1 Point for every 40.000 BHD spent' },
                { icon: '🎁', title: 'Redeem awards', desc: 'Redeem meals, free nights, and shopping gift vouchers' }
            ];
        }
    };

    const benefits = getBenefits();

    return (
        <ScrollView 
            style={styles.container}
            refreshControl={
                <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#fbbf24" />
            }
        >
            {/* Header Greeting */}
            <View style={styles.header}>
                <View>
                    <Text style={styles.greetText}>Good day,</Text>
                    <Text style={styles.nameText}>{member.title} {member.last_name}</Text>
                </View>
                <View style={styles.badgeContainer}>
                    <Text style={styles.statusBadge}>ACTIVE MEMBER</Text>
                </View>
            </View>

            {/* Loyalty Card component */}
            <LoyaltyCard member={member} />

            {/* Stats Dashboard Grid */}
            <View style={styles.statsGrid}>
                {member.membership_type === 'K Reward' ? (
                    <TouchableOpacity style={styles.statBox} onPress={onNavigateToPoints}>
                        <Text style={styles.statLabel}>POINTS BALANCE</Text>
                        {loading ? (
                            <ActivityIndicator size="small" color="#fbbf24" />
                        ) : (
                            <Text style={styles.statValue}>{pointsBalance} Pts</Text>
                        )}
                        <Text style={styles.statLink}>View points history →</Text>
                    </TouchableOpacity>
                ) : (
                    <View style={styles.statBox}>
                        <Text style={styles.statLabel}>MEMBERSHIP TIER</Text>
                        <Text style={[styles.statValue, { color: '#fbbf24' }]}>{member.card_type}</Text>
                        <Text style={styles.statLink}>Direct discount activated</Text>
                    </View>
                )}

                <TouchableOpacity style={styles.statBox} onPress={onNavigateToVouchers}>
                    <Text style={styles.statLabel}>ACTIVE VOUCHERS</Text>
                    {loading ? (
                        <ActivityIndicator size="small" color="#fbbf24" />
                    ) : (
                        <Text style={styles.statValue}>{vouchersCount} Vouchers</Text>
                    )}
                    <Text style={styles.statLink}>View digital wallet →</Text>
                </TouchableOpacity>
            </View>

            {/* Tier Benefits */}
            <View style={styles.section}>
                <Text style={styles.sectionTitle}>YOUR PROGRAM BENEFITS</Text>
                {benefits.map((b, index) => (
                    <View key={index} style={styles.benefitRow}>
                        <View style={styles.benefitIconBox}>
                            <Text style={styles.benefitIcon}>{b.icon}</Text>
                        </View>
                        <View style={styles.benefitDetails}>
                            <Text style={styles.benefitTitle}>{b.title}</Text>
                            <Text style={styles.benefitDesc}>{b.desc}</Text>
                        </View>
                    </View>
                ))}
            </View>

            {/* Notice Footer */}
            <View style={styles.noticeSection}>
                <Text style={styles.noticeTitle}>T&C highlights:</Text>
                <Text style={styles.noticeText}>
                    • Discounts apply before service charge and government taxes.{'\n'}
                    • Cards are strictly non-transferable.{'\n'}
                    • Vouchers must be redeemed at the hotel before their expiry date.
                </Text>
            </View>
        </ScrollView>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#0b0f19',
        padding: 20,
    },
    header: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginTop: 20,
        marginBottom: 10,
    },
    greetText: {
        fontSize: 14,
        color: '#94a3b8',
    },
    nameText: {
        fontSize: 22,
        fontWeight: '800',
        color: '#fff',
    },
    badgeContainer: {
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        paddingVertical: 6,
        paddingHorizontal: 12,
        borderRadius: 20,
        borderWidth: 1,
        borderColor: 'rgba(16, 185, 129, 0.2)',
    },
    statusBadge: {
        color: '#10b981',
        fontSize: 10,
        fontWeight: '700',
        letterSpacing: 1,
    },
    statsGrid: {
        flexDirection: 'row',
        gap: 15,
        marginVertical: 10,
    },
    statBox: {
        flex: 1,
        backgroundColor: '#151f32',
        borderRadius: 14,
        padding: 16,
        borderWidth: 1,
        borderColor: '#1e293b',
        justifyContent: 'space-between',
        height: 110,
    },
    statLabel: {
        color: '#94a3b8',
        fontSize: 9,
        fontWeight: '700',
        letterSpacing: 0.5,
    },
    statValue: {
        color: '#fff',
        fontSize: 20,
        fontWeight: '800',
        marginVertical: 6,
    },
    statLink: {
        color: '#d97706',
        fontSize: 10,
        fontWeight: '600',
    },
    section: {
        backgroundColor: '#151f32',
        borderRadius: 20,
        padding: 20,
        borderWidth: 1,
        borderColor: '#1e293b',
        marginTop: 20,
        marginBottom: 10,
    },
    sectionTitle: {
        color: '#fbbf24',
        fontSize: 13,
        fontWeight: '800',
        letterSpacing: 1,
        marginBottom: 16,
    },
    benefitRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 15,
        gap: 12,
    },
    benefitIconBox: {
        width: 38,
        height: 38,
        borderRadius: 8,
        backgroundColor: '#0b0f19',
        alignItems: 'center',
        justifyContent: 'center',
    },
    benefitIcon: {
        fontSize: 18,
    },
    benefitDetails: {
        flex: 1,
    },
    benefitTitle: {
        color: '#fff',
        fontSize: 14,
        fontWeight: '600',
    },
    benefitDesc: {
        color: '#94a3b8',
        fontSize: 12,
        marginTop: 2,
    },
    noticeSection: {
        marginVertical: 20,
        paddingHorizontal: 10,
        opacity: 0.6,
    },
    noticeTitle: {
        color: '#94a3b8',
        fontSize: 12,
        fontWeight: '700',
    },
    noticeText: {
        color: '#64748b',
        fontSize: 11,
        lineHeight: 16,
        marginTop: 4,
    }
});

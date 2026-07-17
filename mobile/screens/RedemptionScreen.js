import React, { useState, useEffect } from 'react';
import { 
    View, 
    Text, 
    StyleSheet, 
    FlatList, 
    TouchableOpacity, 
    ActivityIndicator, 
    ScrollView, 
    Alert 
} from 'react-native';
import { pointsService } from '../utils/api';

export default function RedemptionScreen({ member }) {
    const [balanceData, setBalanceData] = useState({ current_balance: 0, total_earned: 0, total_redeemed: 0 });
    const [history, setHistory] = useState([]);
    const [catalogue, setCatalogue] = useState({});
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState('store'); // 'store' or 'history'
    const [redeemingItemId, setRedeemingItemId] = useState(null);

    const loadPointsData = async () => {
        try {
            // Load Points Balance
            const balResponse = await pointsService.getBalance();
            if (balResponse.success && balResponse.data) {
                setBalanceData(balResponse.data);
            }

            // Load Ledger History
            const histResponse = await pointsService.getHistory();
            if (histResponse.success && histResponse.data.ledger) {
                setHistory(histResponse.data.ledger);
            }

            // Load Catalogue
            const catResponse = await pointsService.getCatalogue();
            if (catResponse.success && catResponse.data.catalogue) {
                setCatalogue(catResponse.data.catalogue);
            }
        } catch (error) {
            console.log('Error loading points data:', error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (member.membership_type === 'K Reward') {
            loadPointsData();
        }
    }, []);

    const handleRedeem = (awardTitle, cost) => {
        if (balanceData.current_balance < cost) {
            Alert.alert('Insufficient Points', `This award requires ${cost} points. You currently have ${balanceData.current_balance} points.`);
            return;
        }

        Alert.alert(
            'Confirm Redemption',
            `Are you sure you want to redeem "${awardTitle}" for ${cost} points?`,
            [
                { text: 'Cancel', style: 'cancel' },
                { 
                    text: 'Redeem', 
                    onPress: async () => {
                        setRedeemingItemId(awardTitle);
                        try {
                            const response = await pointsService.redeemAward(awardTitle);
                            if (response.success) {
                                Alert.alert('Success', response.message);
                                loadPointsData(); // Reload points balance and ledger
                            } else {
                                Alert.alert('Redemption Failed', response.message);
                            }
                        } catch (error) {
                            Alert.alert('Error', error.message || 'An error occurred during redemption.');
                        } finally {
                            setRedeemingItemId(null);
                        }
                    }
                }
            ]
        );
    };

    if (member.membership_type !== 'K Reward') {
        return (
            <View style={styles.containerCentered}>
                <Text style={styles.errorIcon}>🛡️</Text>
                <Text style={styles.errorText}>Program Not Applicable</Text>
                <Text style={styles.errorSub}>The Point Redemption Store is exclusively for "The K Reward" bookers. "The K Plus" members receive direct high-value discounts on rooms, health club, and dining services.</Text>
            </View>
        );
    }

    // Categorize catalogue items for cleaner visual presentation
    const getCategorizedStore = () => {
        const categories = {
            Meals: [],
            Fitness: [],
            GiftVouchers: [],
            FreeNights: []
        };

        Object.entries(catalogue).forEach(([title, points]) => {
            const item = { title, points };
            if (title.toLowerCase().includes('lunch') || title.toLowerCase().includes('dinner') || title.toLowerCase().includes('brunch')) {
                categories.Meals.push(item);
            } else if (title.toLowerCase().includes('health club') || title.toLowerCase().includes('fitness')) {
                categories.Fitness.push(item);
            } else if (title.toLowerCase().includes('gift voucher')) {
                categories.GiftVouchers.push(item);
            } else if (title.toLowerCase().includes('night')) {
                categories.FreeNights.push(item);
            }
        });

        return categories;
    };

    const categories = getCategorizedStore();

    const renderStoreSection = (catTitle, items) => {
        if (!items || items.length === 0) return null;
        return (
            <View style={styles.storeSection}>
                <Text style={styles.storeSectionTitle}>{catTitle}</Text>
                {items.map((item, index) => {
                    const isRedeeming = redeemingItemId === item.title;
                    const canAfford = balanceData.current_balance >= item.points;
                    return (
                        <View key={index} style={styles.awardCard}>
                            <View style={styles.awardLeft}>
                                <Text style={styles.awardTitle}>{item.title}</Text>
                                <View style={styles.pointsBadge}>
                                    <Text style={styles.pointsBadgeText}>{item.points} POINTS</Text>
                                </View>
                            </View>
                            <TouchableOpacity 
                                style={[
                                    styles.redeemBtn,
                                    !canAfford && styles.redeemBtnDisabled
                                ]}
                                disabled={isRedeeming}
                                onPress={() => handleRedeem(item.title, item.points)}
                            >
                                {isRedeeming ? (
                                    <ActivityIndicator size="small" color="#fff" />
                                ) : (
                                    <Text style={styles.redeemBtnText}>Claim</Text>
                                )}
                            </TouchableOpacity>
                        </View>
                    );
                })}
            </View>
        );
    };

    return (
        <View style={styles.container}>
            {/* Points Summary Banner */}
            <View style={styles.summaryBanner}>
                <Text style={styles.balanceLabel}>YOUR BOOKER REWARD POINTS</Text>
                {loading ? (
                    <ActivityIndicator size="small" color="#fbbf24" style={{ marginVertical: 10 }} />
                ) : (
                    <Text style={styles.balanceVal}>{balanceData.current_balance} <Text style={{ fontSize: 16, fontWeight: '500' }}>Points</Text></Text>
                )}
                
                <View style={styles.statsRow}>
                    <Text style={styles.subStat}>Total Earned: {balanceData.total_earned} Pts</Text>
                    <View style={styles.statLine} />
                    <Text style={styles.subStat}>Redeemed: {balanceData.total_redeemed} Pts</Text>
                </View>
            </View>

            {/* Navigation Tabs */}
            <View style={styles.tabBar}>
                <TouchableOpacity 
                    style={[styles.tabButton, activeTab === 'store' && styles.tabButtonActive]}
                    onPress={() => setActiveTab('store')}
                >
                    <Text style={[styles.tabText, activeTab === 'store' && styles.tabTextActive]}>Redeem Rewards</Text>
                </TouchableOpacity>
                <TouchableOpacity 
                    style={[styles.tabButton, activeTab === 'history' && styles.tabButtonActive]}
                    onPress={() => setActiveTab('history')}
                >
                    <Text style={[styles.tabText, activeTab === 'history' && styles.tabTextActive]}>Points History</Text>
                </TouchableOpacity>
            </View>

            {/* Tab Body */}
            {loading ? (
                <View style={styles.tabBodyCentered}>
                    <ActivityIndicator size="large" color="#fbbf24" />
                </View>
            ) : activeTab === 'store' ? (
                <ScrollView contentContainerStyle={styles.scrollBody}>
                    {renderStoreSection('🍽️ Meal Vouchers at KOLORS & K Lounge', categories.Meals)}
                    {renderStoreSection('💪 Fitness & Health Club Access', categories.Fitness)}
                    {renderStoreSection('🎁 Shopping Gift Vouchers (BHD)', categories.GiftVouchers)}
                    {renderStoreSection('🏨 Complimentary Suite & Deluxe Nights', categories.FreeNights)}
                </ScrollView>
            ) : (
                <FlatList
                    data={history}
                    keyExtractor={item => item.id.toString()}
                    contentContainerStyle={styles.historyList}
                    renderItem={({ item }) => {
                        const isEarned = item.transaction_type === 'Earned';
                        return (
                            <View style={styles.historyCard}>
                                <View style={styles.historyLeft}>
                                    <Text style={styles.historySource}>{item.source}</Text>
                                    <Text style={styles.historyDesc}>{item.description}</Text>
                                    <Text style={styles.historyDate}>Date: {item.transaction_date}</Text>
                                </View>
                                <Text style={[
                                    styles.historyPoints,
                                    { color: isEarned ? '#10b981' : '#ef4444' }
                                ]}>
                                    {isEarned ? `+${item.points_earned}` : `-${item.points_redeemed}`}
                                </Text>
                            </View>
                        );
                    }}
                    ListEmptyComponent={
                        <View style={styles.emptyContainer}>
                            <Text style={styles.emptyText}>No transactions recorded.</Text>
                            <Text style={styles.emptySub}>Book rooms, dining, or events using your membership number to earn points.</Text>
                        </View>
                    }
                />
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#0b0f19',
    },
    containerCentered: {
        flex: 1,
        backgroundColor: '#0b0f19',
        justifyContent: 'center',
        alignItems: 'center',
        padding: 30,
    },
    errorIcon: {
        fontSize: 50,
        marginBottom: 15,
        opacity: 0.8,
    },
    errorText: {
        color: '#fff',
        fontSize: 18,
        fontWeight: '800',
    },
    errorSub: {
        color: '#94a3b8',
        fontSize: 13,
        textAlign: 'center',
        lineHeight: 20,
        marginTop: 10,
    },
    summaryBanner: {
        backgroundColor: '#151f32',
        padding: 24,
        alignItems: 'center',
        borderBottomWidth: 1,
        borderColor: '#1e293b',
    },
    balanceLabel: {
        color: '#94a3b8',
        fontSize: 10,
        fontWeight: '700',
        letterSpacing: 1,
    },
    balanceVal: {
        color: '#fbbf24',
        fontSize: 32,
        fontWeight: '900',
        marginVertical: 8,
    },
    statsRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginTop: 4,
    },
    subStat: {
        color: '#94a3b8',
        fontSize: 11,
    },
    statLine: {
        width: 1,
        height: 12,
        backgroundColor: '#1e293b',
        marginHorizontal: 12,
    },
    tabBar: {
        flexDirection: 'row',
        backgroundColor: '#0e1626',
        borderBottomWidth: 1,
        borderColor: '#1e293b',
    },
    tabButton: {
        flex: 1,
        paddingVertical: 14,
        alignItems: 'center',
        borderBottomWidth: 2,
        borderBottomColor: 'transparent',
    },
    tabButtonActive: {
        borderBottomColor: '#d97706',
    },
    tabText: {
        color: '#64748b',
        fontSize: 14,
        fontWeight: '600',
    },
    tabTextActive: {
        color: '#fbbf24',
    },
    tabBodyCentered: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
    },
    scrollBody: {
        padding: 20,
        paddingBottom: 40,
    },
    storeSection: {
        marginBottom: 25,
    },
    storeSectionTitle: {
        color: '#94a3b8',
        fontSize: 11,
        fontWeight: '800',
        letterSpacing: 0.5,
        marginBottom: 12,
        textTransform: 'uppercase',
    },
    awardCard: {
        backgroundColor: '#151f32',
        borderRadius: 14,
        borderWidth: 1,
        borderColor: '#1e293b',
        padding: 16,
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 10,
    },
    awardLeft: {
        flex: 1,
        marginRight: 15,
    },
    awardTitle: {
        color: '#fff',
        fontSize: 14,
        fontWeight: '600',
        lineHeight: 18,
    },
    pointsBadge: {
        alignSelf: 'flex-start',
        backgroundColor: 'rgba(217, 119, 6, 0.1)',
        paddingVertical: 3,
        paddingHorizontal: 8,
        borderRadius: 6,
        marginTop: 6,
    },
    pointsBadgeText: {
        color: '#fbbf24',
        fontSize: 9,
        fontWeight: '700',
    },
    redeemBtn: {
        backgroundColor: '#d97706',
        paddingVertical: 8,
        paddingHorizontal: 16,
        borderRadius: 8,
        minWidth: 70,
        alignItems: 'center',
    },
    redeemBtnDisabled: {
        backgroundColor: '#334155',
        opacity: 0.4,
    },
    redeemBtnText: {
        color: '#fff',
        fontSize: 12,
        fontWeight: '700',
    },
    historyList: {
        padding: 20,
    },
    historyCard: {
        backgroundColor: '#151f32',
        borderRadius: 12,
        borderWidth: 1,
        borderColor: '#1e293b',
        padding: 16,
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 10,
    },
    historyLeft: {
        flex: 1,
        marginRight: 10,
    },
    historySource: {
        color: '#94a3b8',
        fontSize: 9,
        fontWeight: '700',
        letterSpacing: 0.5,
        textTransform: 'uppercase',
    },
    historyDesc: {
        color: '#fff',
        fontSize: 13,
        fontWeight: '600',
        marginVertical: 4,
    },
    historyDate: {
        color: '#64748b',
        fontSize: 10,
    },
    historyPoints: {
        fontSize: 18,
        fontWeight: '800',
    },
    emptyContainer: {
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 60,
    },
    emptyText: {
        color: '#fff',
        fontSize: 14,
        fontWeight: '700',
    },
    emptySub: {
        color: '#64748b',
        fontSize: 12,
        textAlign: 'center',
        lineHeight: 18,
        marginTop: 6,
        marginHorizontal: 20,
    }
});

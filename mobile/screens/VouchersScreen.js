import React, { useState, useEffect } from 'react';
import { 
    View, 
    Text, 
    StyleSheet, 
    FlatList, 
    TouchableOpacity, 
    ActivityIndicator, 
    Modal,
    RefreshControl 
} from 'react-native';
import { vouchersService } from '../utils/api';

export default function VouchersScreen({ member }) {
    const [vouchers, setVouchers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [selectedVoucher, setSelectedVoucher] = useState(null);

    const loadVouchers = async () => {
        try {
            const response = await vouchersService.getMyVouchers();
            if (response.success && response.data.vouchers) {
                setVouchers(response.data.vouchers);
            }
        } catch (error) {
            console.log('Error loading vouchers:', error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadVouchers();
    }, []);

    const onRefresh = async () => {
        setRefreshing(true);
        await loadVouchers();
        setRefreshing(false);
    };

    const renderVoucherItem = ({ item }) => {
        const isActive = item.status === 'Active';
        return (
            <TouchableOpacity 
                activeOpacity={isActive ? 0.85 : 1}
                onPress={() => isActive && setSelectedVoucher(item)}
                style={[
                    styles.ticket,
                    !isActive && styles.ticketInactive
                ]}
            >
                {/* Left Side (Voucher info) */}
                <View style={styles.ticketLeft}>
                    <View style={styles.headerRow}>
                        <Text style={[styles.voucherTag, { color: isActive ? '#fbbf24' : '#64748b' }]}>
                            {item.voucher_type.toUpperCase()}
                        </Text>
                        <View style={[
                            styles.statusBadge,
                            { backgroundColor: item.status === 'Active' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }
                        ]}>
                            <Text style={[
                                styles.statusText,
                                { color: item.status === 'Active' ? '#10b981' : '#ef4444' }
                            ]}>
                                {item.status}
                            </Text>
                        </View>
                    </View>

                    <Text style={styles.voucherDesc}>{item.description}</Text>
                    
                    <Text style={styles.expiryLabel}>
                        VALID UNTIL: <Text style={styles.expiryDate}>{item.valid_until}</Text>
                    </Text>
                </View>

                {/* Dashed divider */}
                <View style={styles.dashedDivider}>
                    <View style={styles.circleTop} />
                    <View style={styles.circleBottom} />
                </View>

                {/* Right Side (Scan/Use graphic) */}
                <View style={styles.ticketRight}>
                    {isActive ? (
                        <View style={styles.scanActionBox}>
                            <Text style={styles.scanIcon}>📱</Text>
                            <Text style={styles.scanActionText}>TAP TO USE</Text>
                        </View>
                    ) : (
                        <View style={styles.scanActionBox}>
                            <Text style={[styles.scanIcon, { opacity: 0.3 }]}>🔒</Text>
                            <Text style={[styles.scanActionText, { color: '#64748b' }]}>CLOSED</Text>
                        </View>
                    )}
                </View>
            </TouchableOpacity>
        );
    };

    return (
        <View style={styles.container}>
            <View style={styles.subHeader}>
                <Text style={styles.title}>Digital Voucher Wallet</Text>
                <Text style={styles.subtitle}>Showing signup and earned award certificates</Text>
            </View>

            {loading ? (
                <View style={styles.centered}>
                    <ActivityIndicator size="large" color="#fbbf24" />
                </View>
            ) : vouchers.length === 0 ? (
                <View style={styles.centered}>
                    <Text style={styles.emptyIcon}>🎫</Text>
                    <Text style={styles.emptyText}>No vouchers available yet.</Text>
                    <Text style={styles.emptySub}>Vouchers issued for "K Plus" membership or redeemed via "K Reward" points will appear here.</Text>
                </View>
            ) : (
                <FlatList
                    data={vouchers}
                    renderItem={renderVoucherItem}
                    keyExtractor={item => item.id.toString()}
                    contentContainerStyle={styles.listContainer}
                    refreshControl={
                        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#fbbf24" />
                    }
                />
            )}

            {/* Voucher Use Modal */}
            <Modal
                animationType="fade"
                transparent={true}
                visible={!!selectedVoucher}
                onRequestClose={() => setSelectedVoucher(null)}
            >
                <TouchableOpacity 
                    style={styles.modalOverlay} 
                    activeOpacity={1} 
                    onPress={() => setSelectedVoucher(null)}
                >
                    <View style={styles.modalContent}>
                        <Text style={styles.modalType}>{selectedVoucher?.voucher_type}</Text>
                        <Text style={styles.modalDesc}>{selectedVoucher?.description}</Text>
                        <Text style={styles.modalNumber}>Voucher No: {selectedVoucher?.voucher_number}</Text>
                        
                        {/* Mock QR Code for scanning at checkout */}
                        <View style={styles.qrContainer}>
                            <View style={styles.mockQrCode}>
                                <View style={styles.qrCornerBlock} />
                                <View style={[styles.qrCornerBlock, { right: 15 }]} />
                                <View style={[styles.qrCornerBlock, { bottom: 15 }]} />
                                <View style={styles.qrCenterDot} />
                                <Text style={styles.scanCodeText}>{selectedVoucher?.voucher_number}</Text>
                            </View>
                        </View>

                        <Text style={styles.modalNotice}>Present this code to the front office clerk or waiter before finalizing your bill payment.</Text>
                        
                        <TouchableOpacity style={styles.closeBtn} onPress={() => setSelectedVoucher(null)}>
                            <Text style={styles.closeBtnText}>Close Wallet</Text>
                        </TouchableOpacity>
                    </View>
                </TouchableOpacity>
            </Modal>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#0b0f19',
    },
    subHeader: {
        padding: 20,
    },
    title: {
        color: '#fff',
        fontSize: 18,
        fontWeight: '800',
    },
    subtitle: {
        color: '#94a3b8',
        fontSize: 12,
        marginTop: 4,
    },
    listContainer: {
        paddingHorizontal: 20,
        paddingBottom: 40,
    },
    centered: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
        padding: 40,
    },
    emptyIcon: {
        fontSize: 50,
        marginBottom: 15,
        opacity: 0.5,
    },
    emptyText: {
        color: '#fff',
        fontSize: 16,
        fontWeight: '700',
    },
    emptySub: {
        color: '#64748b',
        fontSize: 12,
        textAlign: 'center',
        lineHeight: 18,
        marginTop: 8,
    },
    ticket: {
        backgroundColor: '#151f32',
        borderRadius: 14,
        flexDirection: 'row',
        marginBottom: 16,
        borderWidth: 1,
        borderColor: '#1e293b',
        overflow: 'hidden',
        height: 120,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 6,
        elevation: 3,
    },
    ticketInactive: {
        backgroundColor: 'rgba(21, 31, 50, 0.4)',
        borderColor: '#0f172a',
        opacity: 0.6,
    },
    ticketLeft: {
        flex: 3,
        padding: 16,
        justifyContent: 'space-between',
    },
    headerRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    voucherTag: {
        fontSize: 9,
        fontWeight: '800',
        letterSpacing: 1,
    },
    statusBadge: {
        paddingVertical: 3,
        paddingHorizontal: 8,
        borderRadius: 12,
    },
    statusText: {
        fontSize: 8,
        fontWeight: '700',
    },
    voucherDesc: {
        color: '#fff',
        fontSize: 13,
        fontWeight: '600',
        marginVertical: 6,
    },
    expiryLabel: {
        color: '#64748b',
        fontSize: 8,
        fontWeight: '600',
    },
    expiryDate: {
        color: '#94a3b8',
    },
    dashedDivider: {
        width: 1,
        borderStyle: 'dashed',
        borderWidth: 1,
        borderColor: '#1e293b',
        position: 'relative',
        height: '100%',
        marginHorizontal: 4,
    },
    circleTop: {
        position: 'absolute',
        top: -10,
        left: -10,
        width: 20,
        height: 20,
        borderRadius: 10,
        backgroundColor: '#0b0f19',
    },
    circleBottom: {
        position: 'absolute',
        bottom: -10,
        left: -10,
        width: 20,
        height: 20,
        borderRadius: 10,
        backgroundColor: '#0b0f19',
    },
    ticketRight: {
        flex: 1.1,
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: 'rgba(255,255,255,0.01)',
    },
    scanActionBox: {
        alignItems: 'center',
    },
    scanIcon: {
        fontSize: 22,
        marginBottom: 6,
    },
    scanActionText: {
        color: '#fbbf24',
        fontSize: 9,
        fontWeight: '800',
        letterSpacing: 0.5,
    },
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.85)',
        justifyContent: 'center',
        alignItems: 'center',
        padding: 20,
    },
    modalContent: {
        backgroundColor: '#1e293b',
        borderRadius: 24,
        padding: 30,
        width: '100%',
        maxWidth: 340,
        alignItems: 'center',
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.08)',
    },
    modalType: {
        color: '#fbbf24',
        fontSize: 12,
        fontWeight: '800',
        letterSpacing: 1.5,
    },
    modalDesc: {
        color: '#fff',
        fontSize: 16,
        fontWeight: '700',
        marginTop: 10,
        textAlign: 'center',
    },
    modalNumber: {
        color: '#94a3b8',
        fontSize: 12,
        fontFamily: 'monospace',
        marginTop: 6,
    },
    qrContainer: {
        backgroundColor: '#fff',
        padding: 16,
        borderRadius: 12,
        marginVertical: 20,
    },
    mockQrCode: {
        width: 150,
        height: 150,
        backgroundColor: '#fff',
        position: 'relative',
        justifyContent: 'center',
        alignItems: 'center',
    },
    qrCornerBlock: {
        position: 'absolute',
        width: 35,
        height: 35,
        borderWidth: 8,
        borderColor: '#0f172a',
        top: 10,
        left: 10,
    },
    qrCenterDot: {
        width: 25,
        height: 25,
        backgroundColor: '#0f172a',
    },
    scanCodeText: {
        position: 'absolute',
        bottom: 5,
        fontSize: 8,
        fontWeight: '700',
        color: '#0f172a',
    },
    modalNotice: {
        color: '#94a3b8',
        fontSize: 12,
        textAlign: 'center',
        lineHeight: 18,
    },
    closeBtn: {
        marginTop: 20,
        backgroundColor: '#d97706',
        paddingVertical: 12,
        paddingHorizontal: 40,
        borderRadius: 25,
        width: '100%',
        alignItems: 'center',
    },
    closeBtnText: {
        color: '#fff',
        fontWeight: '700',
        fontSize: 14,
    }
});

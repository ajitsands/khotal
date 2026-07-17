import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';

export default function ProfileScreen({ member, onLogout }) {
    if (!member) return null;

    const { first_name, last_name, email, mobile, id_number, nationality, company_name, position, membership_type, card_type, expiry_date } = member;
    const fullName = `${first_name} ${last_name}`;

    return (
        <ScrollView style={styles.container}>
            {/* Top Avatar Banner */}
            <View style={styles.avatarBanner}>
                <View style={styles.avatarCircle}>
                    <Text style={styles.avatarInitials}>
                        {first_name.charAt(0)}{last_name.charAt(0)}
                    </Text>
                </View>
                <Text style={styles.name}>{fullName}</Text>
                <Text style={styles.memberId}>Card Number: {member.membership_number && member.membership_number.replace(/[\s-]/g, '').length === 16 ? member.membership_number.replace(/[\s-]/g, '').replace(/(.{4})/g, '$1 ').trim() : member.membership_number}</Text>
            </View>

            {/* Profile Info Section */}
            <View style={styles.section}>
                <Text style={styles.sectionTitle}>MEMBER ACCOUNT PROFILE</Text>
                
                <View style={styles.infoRow}>
                    <Text style={styles.label}>EMAIL ADDRESS</Text>
                    <Text style={styles.value}>{email}</Text>
                </View>
                
                <View style={styles.infoRow}>
                    <Text style={styles.label}>MOBILE PHONE</Text>
                    <Text style={styles.value}>{mobile}</Text>
                </View>

                <View style={styles.infoRow}>
                    <Text style={styles.label}>CPR / PASSPORT NO</Text>
                    <Text style={styles.value}>{id_number}</Text>
                </View>

                <View style={styles.infoRow}>
                    <Text style={styles.label}>NATIONALITY</Text>
                    <Text style={styles.value}>{nationality}</Text>
                </View>

                {company_name ? (
                    <View style={styles.infoRow}>
                        <Text style={styles.label}>COMPANY & POSITION</Text>
                        <Text style={styles.value}>{position} at {company_name}</Text>
                    </View>
                ) : null}

                <View style={[styles.infoRow, { borderBottomWidth: 0 }]}>
                    <Text style={styles.label}>EXPIRATION DATE</Text>
                    <Text style={[styles.value, { color: '#fbbf24', fontWeight: '700' }]}>{expiry_date}</Text>
                </View>
            </View>

            {/* Loyalty Terms & Conditions (Page 2 & Page 4 guidelines) */}
            <View style={styles.section}>
                <Text style={styles.sectionTitle}>TERMS & CONDITIONS GUIDELINES</Text>
                <Text style={styles.termsText}>
                    1. Enrollment and signing of required documents is necessary for participation.{'\n\n'}
                    2. Vouchers and membership cards are strictly non-transferable.{'\n\n'}
                    3. Discounts do not apply to ongoing promotions or special hotel offers (e.g. Happy Hour, Business Lunch, Valentine, Eid, Christmas packages, etc.).{'\n\n'}
                    4. The K Plus membership cards cannot be used as credit cards.{'\n\n'}
                    5. Loyalty points earned under "The K Reward" are valid for one (1) year from the date of checkout.{'\n\n'}
                    6. Vouchers for rooms or dining must be presented at checkout for redemption, subject to booking availability.
                </Text>
            </View>

            {/* Logout Button */}
            <TouchableOpacity style={styles.logoutButton} onPress={onLogout}>
                <Text style={styles.logoutText}>Sign Out Account</Text>
            </TouchableOpacity>

            <Text style={styles.versionText}>The K Hotel Loyalty App • Version 1.0.0</Text>
        </ScrollView>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#0b0f19',
        padding: 20,
    },
    avatarBanner: {
        alignItems: 'center',
        marginVertical: 20,
    },
    avatarCircle: {
        width: 70,
        height: 70,
        borderRadius: 35,
        backgroundColor: '#d97706',
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 12,
    },
    avatarInitials: {
        color: '#fff',
        fontSize: 24,
        fontWeight: '700',
    },
    name: {
        color: '#fff',
        fontSize: 18,
        fontWeight: '700',
    },
    memberId: {
        color: '#94a3b8',
        fontSize: 13,
        marginTop: 4,
        fontFamily: 'monospace',
    },
    section: {
        backgroundColor: '#151f32',
        borderRadius: 16,
        padding: 20,
        borderWidth: 1,
        borderColor: '#1e293b',
        marginBottom: 20,
    },
    sectionTitle: {
        color: '#fbbf24',
        fontSize: 11,
        fontWeight: '800',
        letterSpacing: 1,
        marginBottom: 16,
        textTransform: 'uppercase',
    },
    infoRow: {
        borderBottomWidth: 1,
        borderBottomColor: '#1e293b',
        paddingVertical: 12,
    },
    label: {
        color: '#64748b',
        fontSize: 9,
        fontWeight: '700',
        letterSpacing: 0.5,
    },
    value: {
        color: '#fff',
        fontSize: 14,
        fontWeight: '500',
        marginTop: 4,
    },
    termsText: {
        color: '#94a3b8',
        fontSize: 11,
        lineHeight: 18,
    },
    logoutButton: {
        borderWidth: 1,
        borderColor: '#ef4444',
        borderRadius: 10,
        paddingVertical: 14,
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 20,
    },
    logoutText: {
        color: '#ef4444',
        fontSize: 14,
        fontWeight: '700',
    },
    versionText: {
        textAlign: 'center',
        color: '#475569',
        fontSize: 11,
        marginBottom: 40,
    }
});

from argon2 import PasswordHasher
from argon2.exceptions import VerifyMismatchError
import sys

# Tạo đối tượng băm mật khẩu
ph = PasswordHasher()

def bam_matkhau(matkhau_goc: str) -> str:
    return ph.hash(matkhau_goc)

def kiemtra_matkhau(matkhau_nhapvao: str, matkhau_dabam: str) -> bool:
    try:
        return ph.verify(matkhau_dabam, matkhau_nhapvao)
    except VerifyMismatchError:
        return False

# Xử lý command line arguments
if __name__ == "__main__":
    if len(sys.argv) == 3:
        # Kiểm tra mật khẩu: python password_hash.py <'password'> <'hashed_password'>
        password = sys.argv[1]
        hashed_password = sys.argv[2]
        result = kiemtra_matkhau(password, hashed_password)
        print(str(result).lower())
    elif len(sys.argv) == 2:
        # Băm mật khẩu: python password_hash.py <'password'>
        password = sys.argv[1]
        hashed = bam_matkhau(password)
        print(hashed)
    else:
        print("Usage:")
        print("  To hash password: python password_hash.py <password>")
        print("  To verify password: python password_hash.py <password> <hashed_password>")